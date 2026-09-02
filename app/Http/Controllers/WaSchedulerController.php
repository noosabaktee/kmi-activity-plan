<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MSetting;
use App\Models\MUser;
use App\Models\MWaSchedule;
use App\Models\TrWaLog;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaSchedulerController extends Controller
{
    public function index(): View
    {
        $schedules = MWaSchedule::with(['department', 'user'])->active()->orderBy('txtScheduledTime')->get();
        $logs = TrWaLog::with('user')->orderBy('dtmSentAt', 'desc')->limit(50)->get();
        $authUser = MUser::find(session('auth_user_id'));
        $departments = MDepartment::active()->orderBy('txtDepartmentName')->get();
        $users = MUser::with(['department', 'subDepartment'])->active()->orderBy('txtEmployeeName')->get();

        $settings = [
            'wa_api_url' => MSetting::get('wa_api_url', config('services.whatsapp.url', env('WA_API_URL', 'https://whatsapp.intconnect.id/send-message'))),
            'wa_api_key' => MSetting::get('wa_api_key', config('services.whatsapp.api_key', env('WA_API_KEY', 'kmi_wa_api_key_2026'))),
            'wa_sender' => MSetting::get('wa_sender', config('services.whatsapp.sender', env('WA_SENDER', '6281234567890'))),
            'wa_footer' => MSetting::get('wa_footer', 'Sent via KMI Activity Plan'),
        ];

        return view('wa-scheduler.index', [
            'schedules' => $schedules,
            'logs' => $logs,
            'authUser' => $authUser,
            'departments' => $departments,
            'users' => $users,
            'settings' => $settings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtScheduleTitle' => ['required', 'string', 'max:200'],
            'txtCronDay' => ['required', 'string', 'max:50'],
            'txtScheduledTime' => ['required', 'string', 'max:10'],
            'intDepartment_ID' => ['nullable', 'exists:mDepartment,intDepartment_ID'],
            'txtTargetRecipient' => ['required', 'string', 'max:100'],
            'txtMessageTemplate' => ['required', 'string'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));
        $targetRecipient = $validated['txtTargetRecipient'];

        $targetUserId = null;
        $targetRole = 'All';
        $targetType = 'all';

        if (str_starts_with($targetRecipient, 'USER_')) {
            $targetUserId = (int) substr($targetRecipient, 5);
            $targetRole = null;
            $targetType = 'user';
        } elseif ($targetRecipient === 'Employee' || $targetRecipient === 'Employee Only') {
            $targetRole = 'Employee';
            $targetType = 'role';
        } elseif ($targetRecipient === 'Supervisor' || $targetRecipient === 'Supervisor Only') {
            $targetRole = 'Supervisor';
            $targetType = 'role';
        } else {
            $targetRole = 'All';
            $targetType = 'all';
        }

        MWaSchedule::create([
            'txtScheduleTitle' => $validated['txtScheduleTitle'],
            'txtCronDay' => $validated['txtCronDay'],
            'txtCronExpression' => 'Setiap ' . $validated['txtCronDay'],
            'txtScheduledTime' => $validated['txtScheduledTime'],
            'intDepartment_ID' => !empty($validated['intDepartment_ID']) ? (int) $validated['intDepartment_ID'] : null,
            'intUser_ID' => $targetUserId,
            'txtTargetRole' => $targetRole,
            'txtTargetType' => $targetType,
            'txtMessageTemplate' => $validated['txtMessageTemplate'],
            'txtFooterText' => MSetting::get('wa_footer', 'Sent via KMI Activity Plan'),
            'bitActive' => 1,
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'Superadmin',
            'dtmInserted' => now(),
        ]);

        return redirect()->route('wa-scheduler.index')->with('success', 'Jadwal WhatsApp pengingat berhasil ditambahkan.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wa_api_url' => ['required', 'url'],
            'wa_api_key' => ['required', 'string'],
            'wa_sender' => ['required', 'string'],
            'wa_footer' => ['nullable', 'string', 'max:150'],
        ]);

        $user = session('auth_user_name', 'Superadmin');

        MSetting::set('wa_api_url', $validated['wa_api_url'], 'whatsapp', 'WhatsApp Gateway API Endpoint URL', $user);
        MSetting::set('wa_api_key', $validated['wa_api_key'], 'whatsapp', 'WhatsApp Gateway API Key', $user);
        MSetting::set('wa_sender', $validated['wa_sender'], 'whatsapp', 'WhatsApp Gateway Sender Number / Device ID', $user);
        MSetting::set('wa_footer', $validated['wa_footer'] ?? 'Sent via KMI Activity Plan', 'whatsapp', 'WhatsApp Footer Text', $user);

        return redirect()->route('wa-scheduler.index')->with('success', 'Konfigurasi API WhatsApp berhasil disimpan dan diperbarui!');
    }

    public function destroy(MWaSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('wa-scheduler.index')->with('success', 'Jadwal WhatsApp berhasil dihapus.');
    }

    public function trigger(MWaSchedule $schedule): RedirectResponse
    {
        $result = WhatsAppService::broadcastSchedule($schedule);

        return redirect()->route('wa-scheduler.index')->with('success', "Broadcast berhasil diproses ke {$result['sent']} nomor ({$result['failed']} gagal). Total target: {$result['total']}.");
    }

    public function testSend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $result = WhatsAppService::sendMessage($validated['phone'], $validated['message']);

        if ($result['status'] === 'Success') {
            return redirect()->route('wa-scheduler.index')->with('success', 'Pesan WhatsApp uji coba berhasil dikirim ke ' . $validated['phone']);
        }

        return redirect()->route('wa-scheduler.index')->with('error', 'Gagal mengirim pesan: ' . ($result['response'] ?? 'API error'));
    }
}
