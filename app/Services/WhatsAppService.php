<?php

namespace App\Services;

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

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url', env('WA_API_URL', 'https://whatsapp.intconnect.id/send-message'));
        $this->apiKey = config('services.whatsapp.api_key', env('WA_API_KEY', 'kmi_wa_api_key_2026'));
        $this->sender = config('services.whatsapp.sender', env('WA_SENDER', '6281234567890'));
    }

    /**
     * Send WhatsApp message to a specific number or user.
     */
    public function sendMessage(
        string $phoneNumber,
        string $message,
        ?string $footer = 'Sent via KMI Activity Plan',
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
            'footer' => $footer ?: 'Sent via KMI Activity Plan',
        ];

        $status = 'PENDING';
        $responseBody = null;

        try {
            $response = Http::timeout(15)->get($this->apiUrl, $params);
            $responseBody = $response->body();
            $status = ($response->successful() || str_contains(strtolower($responseBody), 'true') || str_contains(strtolower($responseBody), 'success'))
                ? 'SUCCESS'
                : 'FAILED';
        } catch (Throwable $e) {
            $status = 'FAILED';
            $responseBody = $e->getMessage();
            Log::warning("WhatsApp send failed for {$cleanPhone}: " . $e->getMessage());
        }

        // Record log
        TrWaLog::create([
            'intWaSchedule_ID' => $scheduleId,
            'intUser_ID' => $userId,
            'txtRecipientPhone' => $cleanPhone,
            'txtRecipientName' => $recipientName ?: "User #{$userId}",
            'txtMessage' => $message,
            'txtStatus' => $status,
            'txtApiResponse' => $responseBody,
            'dtmSentAt' => $now,
            'txtInsertedBy' => auth()->user()?->txtEmail ?? 'system',
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
    public function executeSchedule(MWaSchedule $schedule): int
    {
        $query = MUser::where('bitActive', true)->whereNotNull('txtPhone');

        if ($schedule->txtTargetType === 'subdept' && $schedule->intSubDepartment_ID) {
            $query->where('intSubDepartment_ID', $schedule->intSubDepartment_ID);
        } elseif ($schedule->txtTargetType === 'role' && $schedule->txtTargetRole) {
            $query->where('txtRole', $schedule->txtTargetRole);
        }

        $employees = $query->get();
        $sentCount = 0;

        foreach ($employees as $emp) {
            if (empty($emp->txtPhone)) {
                continue;
            }

            // Replace dynamic placeholders
            $message = str_replace(
                ['{employee_name}', '{department}', '{date}', '{subdept}'],
                [
                    $emp->txtEmployeeName,
                    $emp->department?->txtDepartmentName ?: 'Department',
                    now()->translatedFormat('l, d F Y'),
                    $emp->subDepartment?->txtSubDepartmentName ?: '-',
                ],
                $schedule->txtMessageTemplate
            );

            $this->sendMessage(
                phoneNumber: $emp->txtPhone,
                message: $message,
                footer: $schedule->txtFooterText,
                userId: $emp->intUser_ID,
                scheduleId: $schedule->intWaSchedule_ID,
                recipientName: $emp->txtEmployeeName
            );

            $sentCount++;
        }

        $schedule->dtmLastSentAt = now();
        $schedule->save();

        return $sentCount;
    }
}
