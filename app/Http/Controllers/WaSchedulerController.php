<?php

namespace App\Http\Controllers;

use App\Models\MWaSchedule;
use App\Models\MUser;
use App\Models\TrWaLog;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaSchedulerController extends Controller
{
    public function index(): View
    {
        $schedules = MWaSchedule::active()->orderBy('txtScheduledTime')->get();
        $logs = TrWaLog::with('user')->orderBy('dtmSentAt', 'desc')->limit(50)->get();
        $authUser = MUser::find(session('auth_user_id'));

        return view('wa-scheduler.index', [
            'schedules' => $schedules,
            'logs' => $logs,
            'authUser' => $authUser,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtScheduleTitle' => ['required', 'string', 'max:200'],
            'txtCronExpression' => ['required', 'string', 'max:100'],
            'txtScheduledTime' => ['required', 'string', 'max:10'],
            'txtMessageTemplate' => ['required', 'string'],
            'txtTargetRole' => ['required', 'string', 'max:50'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));

        MWaSchedule::create([
            'txtScheduleTitle' => $validated['txtScheduleTitle'],
            'txtCronExpression' => $validated['txtCronExpression'],
            'txtScheduledTime' => $validated['txtScheduledTime'],
            'txtMessageTemplate' => $validated['txtMessageTemplate'],
            'txtTargetRole' => $validated['txtTargetRole'],
            'bitIsActive' => 1,
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'Superadmin',
        ]);

        return redirect()->route('wa-scheduler.index')->with('success', 'Jadwal WhatsApp berhasil ditambahkan.');
    }

    public function destroy(MWaSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('wa-scheduler.index')->with('success', 'Jadwal WhatsApp berhasil dihapus.');
    }

    public function trigger(MWaSchedule $schedule): RedirectResponse
    {
        $result = WhatsAppService::broadcastSchedule($schedule);

        return redirect()->route('wa-scheduler.index')->with('success', "Broadcast berhasil dikirim ke {$result['sent']} nomor. ({$result['failed']} gagal).");
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

        return redirect()->route('wa-scheduler.index')->with('error', 'Gagal mengirim pesan: ' . ($result['error'] ?? 'API error'));
    }
}
