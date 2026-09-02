<?php

namespace App\Services;

use App\Models\MSetting;
use App\Models\MUser;
use App\Models\MWaSchedule;
use App\Models\TrWaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $sender;
    protected string $defaultFooter;

    public function __construct()
    {
        $this->apiUrl = MSetting::get('wa_api_url', config('services.whatsapp.url', env('WA_API_URL', 'https://whatsapp.intconnect.id/send-message')));
        $this->apiKey = MSetting::get('wa_api_key', config('services.whatsapp.api_key', env('WA_API_KEY', 'kmi_wa_api_key_2026')));
        $this->sender = MSetting::get('wa_sender', config('services.whatsapp.sender', env('WA_SENDER', '6281234567890')));
        $this->defaultFooter = MSetting::get('wa_footer', 'Sent via KMI Activity Plan');
    }

    /**
     * Send WhatsApp message to a specific number.
     */
    public function send(
        string $phoneNumber,
        string $message,
        ?string $footer = null,
        ?int $userId = null,
        ?int $scheduleId = null,
        ?string $recipientName = null
    ): array {
        // Sanitize phone number (remove +, spaces, leading 0 to 62)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $now = now();
        $params = [
            'api_key' => $this->apiKey,
            'sender' => $this->sender,
            'number' => $cleanPhone,
            'message' => $message,
            'footer' => $footer ?: $this->defaultFooter,
        ];

        $status = 'PENDING';
        $responseBody = null;

        try {
            $response = Http::timeout(15)->get($this->apiUrl, $params);
            $responseBody = $response->body();
            $status = ($response->successful() || str_contains(strtolower($responseBody), 'true') || str_contains(strtolower($responseBody), 'success'))
                ? 'Success'
                : 'Failed';
        } catch (Throwable $e) {
            $status = 'Failed';
            $responseBody = $e->getMessage();
            Log::warning("WhatsApp send failed for {$cleanPhone}: " . $e->getMessage());
        }

        // Record log into trWaLog
        TrWaLog::create([
            'intWaSchedule_ID' => $scheduleId,
            'intUser_ID' => $userId,
            'txtRecipientPhone' => $cleanPhone,
            'txtRecipientName' => $recipientName ?: ($userId ? "User #{$userId}" : $cleanPhone),
            'txtMessage' => $message,
            'txtStatus' => $status,
            'txtApiResponse' => $responseBody,
            'dtmSentAt' => $now,
            'txtInsertedBy' => session('auth_user_name', 'System Superadmin'),
            'dtmInserted' => $now,
        ]);

        return [
            'status' => $status,
            'response' => $responseBody,
            'phone' => $cleanPhone,
        ];
    }

    /**
     * Execute a scheduled WA notification broadcast to all targeted employees.
     */
    public function executeSchedule(MWaSchedule $schedule): array
    {
        $query = MUser::with(['department', 'subDepartment'])
            ->where('bitActive', true)
            ->whereNotNull('txtPhone')
            ->where('txtPhone', '!=', '');

        // 1. Department filter
        if (!empty($schedule->intDepartment_ID)) {
            $query->where('intDepartment_ID', $schedule->intDepartment_ID);
        }

        // 2. Target recipient filter
        if (!empty($schedule->intUser_ID)) {
            // Specific recipient
            $query->where('intUser_ID', $schedule->intUser_ID);
        } elseif (!empty($schedule->txtTargetRole) && !in_array($schedule->txtTargetRole, ['All', 'Semua', ''])) {
            if ($schedule->txtTargetRole === 'Supervisor' || $schedule->txtTargetRole === 'Supervisor Only') {
                $query->whereIn('txtRole', ['Supervisor', 'Head']);
            } elseif ($schedule->txtTargetRole === 'Employee' || $schedule->txtTargetRole === 'Employee Only') {
                $query->where('txtRole', 'Employee');
            } else {
                $query->where('txtRole', $schedule->txtTargetRole);
            }
        }

        $employees = $query->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($employees as $emp) {
            if (empty($emp->txtPhone)) {
                continue;
            }

            // Dynamic placeholder replacement
            $message = str_replace(
                ['{employee_name}', '{department}', '{date}', '{day}', '{subdept}'],
                [
                    $emp->txtEmployeeName,
                    $emp->department?->txtDepartmentName ?: 'Department',
                    now()->translatedFormat('l, d F Y'),
                    now()->translatedFormat('l'),
                    $emp->subDepartment?->txtSubDepartmentName ?: '-',
                ],
                $schedule->txtMessageTemplate
            );

            $res = $this->send(
                phoneNumber: $emp->txtPhone,
                message: $message,
                footer: $schedule->txtFooterText,
                userId: $emp->intUser_ID,
                scheduleId: $schedule->intWaSchedule_ID,
                recipientName: $emp->txtEmployeeName
            );

            if ($res['status'] === 'Success') {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        $schedule->update([
            'dtmLastSentAt' => now(),
            'dtmUpdated' => now(),
        ]);

        return [
            'total' => $employees->count(),
            'sent' => $sentCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * Static helper for sending single WhatsApp message.
     */
    public static function sendMessage(
        string $phoneNumber,
        string $message,
        ?string $footer = null,
        ?int $userId = null,
        ?int $scheduleId = null,
        ?string $recipientName = null
    ): array {
        return (new static())->send($phoneNumber, $message, $footer, $userId, $scheduleId, $recipientName);
    }

    /**
     * Static helper for broadcasting a schedule.
     */
    public static function broadcastSchedule(MWaSchedule $schedule): array
    {
        return (new static())->executeSchedule($schedule);
    }
}
