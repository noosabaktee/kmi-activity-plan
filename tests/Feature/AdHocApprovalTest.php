<?php

use App\Models\MProject;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrWaLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('employee submitting a new ad hoc initiative sets status to Pending Approval and sends WhatsApp notification to supervisor', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'message' => 'Message sent successfully'], 200),
    ]);

    $employee = MUser::where('txtRole', 'Employee')->first();
    $subDept = MSubDepartment::find($employee->intSubDepartment_ID) ?? MSubDepartment::first();
    $supervisor = MUser::where('txtRole', 'Supervisor')->whereNotNull('txtPhone')->first();

    $payload = [
        'txtProjectName' => 'Perbaikan Kebocoran Pipa Steam Boiler 2',
        'txtAdHocCategory' => 'Emergency Response',
        'txtPriority' => 'Critical',
        'txtSpecialGoal' => 'Mengatasi kebocoran pipa steam sebelum shift 1 berakhir.',
        'txtDescription' => 'Terjadi penurunan tekanan steam secara tiba-tiba di area utilitas.',
        'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
        'intSupervisor_ID' => $supervisor->intUser_ID,
        'intUser_ID' => $employee->intUser_ID,
        'dtmProjectStartDate' => date('Y-m-d'),
        'dtmProjectEndDate' => date('Y-m-d', strtotime('+3 days')),
        'txtDeliverable' => 'Pipa diperbaiki dan tekanan steam normal kembali',
        'floatWeight' => 10.0,
        'stages' => [
            [
                'step' => 'Isolasi valve steam dan perbaikan pipa',
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d', strtotime('+1 days')),
                'plan' => 100,
                'actual' => 0,
            ],
        ],
    ];

    $response = $this->withSession(['auth_user_id' => $employee->intUser_ID])
        ->post(route('adhocs.store'), $payload);

    $response->assertRedirect(route('adhocs.index'));
    $response->assertSessionHas('success');

    $adhoc = MProject::where('txtProjectName', 'Perbaikan Kebocoran Pipa Steam Boiler 2')->first();
    expect($adhoc)->not->toBeNull();
    expect($adhoc->txtApprovalStatus)->toBe('Pending Approval');
    expect($adhoc->txtStatus)->toBe('Pending');
    expect($adhoc->intSupervisor_ID)->toBe($supervisor->intUser_ID);
    expect($adhoc->isPendingApproval())->toBeTrue();
    expect($adhoc->isApproved())->toBeFalse();

    // Verify WhatsApp notification was sent to supervisor and logged
    $cleanPhone = preg_replace('/[^0-9]/', '', $supervisor->txtPhone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }

    $waLog = TrWaLog::where('txtRecipientPhone', $cleanPhone)->latest('dtmInserted')->first();
    expect($waLog)->not->toBeNull();
    expect($waLog->txtMessage)->toContain('NOTIFIKASI PENGAJUAN AD HOC BARU');
    expect($waLog->txtMessage)->toContain($adhoc->txtProjectCode);
    expect($waLog->txtMessage)->toContain('Perbaikan Kebocoran Pipa Steam Boiler 2');
    expect($waLog->txtStatus)->toBe('Success');
});

test('supervisor can approve (ACC) a pending ad hoc initiative and notify requester via WhatsApp', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'message' => 'Message sent successfully'], 200),
    ]);

    $employee = MUser::where('txtRole', 'Employee')->whereNotNull('txtPhone')->first();
    $supervisor = MUser::where('txtRole', 'Supervisor')->first();

    $adhoc = MProject::create([
        'intDepartment_ID' => $employee->intDepartment_ID ?: 1,
        'intSubDepartment_ID' => $employee->intSubDepartment_ID ?: 1,
        'intProjectType_ID' => 5,
        'intUser_ID' => $employee->intUser_ID,
        'intSupervisor_ID' => $supervisor->intUser_ID,
        'txtProjectCode' => 'ADH-2026-888',
        'txtProjectName' => 'Penggantian Sensor Level Tank 4',
        'txtKpiLevel' => 'Ad Hoc',
        'txtSpecialGoal' => 'Akurasi sensor level tangki kembali 100%.',
        'txtPriority' => 'High',
        'txtApprovalStatus' => 'Pending Approval',
        'txtStatus' => 'Pending',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(5),
        'bitActive' => true,
        'bitIsAdHoc' => true,
    ]);

    // Supervisor ACC the ad hoc with notes
    $approveResponse = $this->withSession(['auth_user_id' => $supervisor->intUser_ID])
        ->post(route('adhocs.approve', $adhoc), [
            'txtApprovalNotes' => 'Disetujui. Laksanakan sesuai SOP K3 dan koordinasikan dengan tim maintenance.',
        ]);

    $approveResponse->assertRedirect(route('adhocs.show', $adhoc));
    $approveResponse->assertSessionHas('success');

    $adhoc->refresh();
    expect($adhoc->txtApprovalStatus)->toBe('Approved');
    expect($adhoc->txtStatus)->toBe('In Progress');
    expect($adhoc->intApprovedBy_ID)->toBe($supervisor->intUser_ID);
    expect($adhoc->dtmApprovedAt)->not->toBeNull();
    expect($adhoc->txtApprovalNotes)->toContain('Disetujui. Laksanakan sesuai SOP K3');
    expect($adhoc->isApproved())->toBeTrue();
    expect($adhoc->isPendingApproval())->toBeFalse();

    // Verify WhatsApp confirmation was sent to employee
    $cleanEmployeePhone = preg_replace('/[^0-9]/', '', $employee->txtPhone);
    if (str_starts_with($cleanEmployeePhone, '0')) {
        $cleanEmployeePhone = '62' . substr($cleanEmployeePhone, 1);
    }

    $confirmationLog = TrWaLog::where('txtRecipientPhone', $cleanEmployeePhone)->latest('dtmInserted')->first();
    expect($confirmationLog)->not->toBeNull();
    expect($confirmationLog->txtMessage)->toContain('INISIATIF AD HOC DISETUJUI (ACC)');
    expect($confirmationLog->txtMessage)->toContain($adhoc->txtProjectCode);
});

test('supervisor can reject a pending ad hoc initiative with required revision notes', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'message' => 'Message sent successfully'], 200),
    ]);

    $employee = MUser::where('txtRole', 'Employee')->whereNotNull('txtPhone')->first();
    $supervisor = MUser::where('txtRole', 'Supervisor')->first();

    $adhoc = MProject::create([
        'intDepartment_ID' => $employee->intDepartment_ID ?: 1,
        'intSubDepartment_ID' => $employee->intSubDepartment_ID ?: 1,
        'intProjectType_ID' => 5,
        'intUser_ID' => $employee->intUser_ID,
        'intSupervisor_ID' => $supervisor->intUser_ID,
        'txtProjectCode' => 'ADH-2026-889',
        'txtProjectName' => 'Modifikasi Skema Jalur Konveyor',
        'txtKpiLevel' => 'Ad Hoc',
        'txtSpecialGoal' => 'Merubah layout konveyor sementara.',
        'txtPriority' => 'Medium',
        'txtApprovalStatus' => 'Pending Approval',
        'txtStatus' => 'Pending',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(7),
        'bitActive' => true,
        'bitIsAdHoc' => true,
    ]);

    // Rejection without notes must fail validation
    $invalidResponse = $this->withSession(['auth_user_id' => $supervisor->intUser_ID])
        ->post(route('adhocs.reject', $adhoc), [
            'txtApprovalNotes' => '',
        ]);
    $invalidResponse->assertSessionHasErrors('txtApprovalNotes');

    // Rejection with notes succeeds
    $rejectResponse = $this->withSession(['auth_user_id' => $supervisor->intUser_ID])
        ->post(route('adhocs.reject', $adhoc), [
            'txtApprovalNotes' => 'Rencana tindakan belum lengkap, mohon sertakan analisa risiko keselamatan kerja.',
        ]);

    $rejectResponse->assertRedirect(route('adhocs.show', $adhoc));
    $rejectResponse->assertSessionHas('error');

    $adhoc->refresh();
    expect($adhoc->txtApprovalStatus)->toBe('Rejected');
    expect($adhoc->txtStatus)->toBe('Pending');
    expect($adhoc->intApprovedBy_ID)->toBe($supervisor->intUser_ID);
    expect($adhoc->txtApprovalNotes)->toContain('analisa risiko keselamatan kerja');
    expect($adhoc->isRejected())->toBeTrue();
    expect($adhoc->isApproved())->toBeFalse();
});

test('unauthorized employee cannot approve or reject someone else ad hoc initiative', function () {
    $creator = MUser::where('txtRole', 'Employee')->first();
    $otherEmployee = MUser::where('txtRole', 'Employee')->where('intUser_ID', '!=', $creator->intUser_ID)->first();

    $adhoc = MProject::create([
        'intDepartment_ID' => $creator->intDepartment_ID ?: 1,
        'intSubDepartment_ID' => $creator->intSubDepartment_ID ?: 1,
        'intProjectType_ID' => 5,
        'intUser_ID' => $creator->intUser_ID,
        'txtProjectCode' => 'ADH-2026-890',
        'txtProjectName' => 'Ad Hoc Percobaan Otorisasi',
        'txtKpiLevel' => 'Ad Hoc',
        'txtSpecialGoal' => 'Tes otorisasi approval.',
        'txtPriority' => 'Low',
        'txtApprovalStatus' => 'Pending Approval',
        'txtStatus' => 'Pending',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(3),
        'bitActive' => true,
        'bitIsAdHoc' => true,
    ]);

    // Other employee attempting to approve
    $response = $this->withSession(['auth_user_id' => $otherEmployee->intUser_ID])
        ->post(route('adhocs.approve', $adhoc));

    $response->assertStatus(403);

    // Other employee attempting to reject
    $rejectResponse = $this->withSession(['auth_user_id' => $otherEmployee->intUser_ID])
        ->post(route('adhocs.reject', $adhoc), ['txtApprovalNotes' => 'Mau reject']);

    $rejectResponse->assertStatus(403);
});

test('ad hoc index displays approval filter and pending counter correctly', function () {
    $user = MUser::where('txtRole', 'Superadmin')->first();

    $adhocPending = MProject::create([
        'intDepartment_ID' => 1,
        'intSubDepartment_ID' => 1,
        'intProjectType_ID' => 5,
        'intUser_ID' => $user->intUser_ID,
        'txtProjectCode' => 'ADH-2026-901',
        'txtProjectName' => 'Inisiatif Ad Hoc Menunggu ACC',
        'txtKpiLevel' => 'Ad Hoc',
        'txtSpecialGoal' => 'Sasaran pending test',
        'txtPriority' => 'High',
        'txtApprovalStatus' => 'Pending Approval',
        'txtStatus' => 'Pending',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(2),
        'bitActive' => true,
        'bitIsAdHoc' => true,
    ]);

    $adhocApproved = MProject::create([
        'intDepartment_ID' => 1,
        'intSubDepartment_ID' => 1,
        'intProjectType_ID' => 5,
        'intUser_ID' => $user->intUser_ID,
        'txtProjectCode' => 'ADH-2026-902',
        'txtProjectName' => 'Inisiatif Ad Hoc Sudah ACC',
        'txtKpiLevel' => 'Ad Hoc',
        'txtSpecialGoal' => 'Sasaran approved test',
        'txtPriority' => 'Low',
        'txtApprovalStatus' => 'Approved',
        'txtStatus' => 'In Progress',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(5),
        'bitActive' => true,
        'bitIsAdHoc' => true,
    ]);

    // Access index with approval_status=Pending Approval
    $pendingResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.index', ['approval_status' => 'Pending Approval']));

    $pendingResponse->assertStatus(200);
    $pendingResponse->assertSee('Inisiatif Ad Hoc Menunggu ACC');
    $pendingResponse->assertSee('Menunggu ACC');
    $pendingResponse->assertDontSee('Inisiatif Ad Hoc Sudah ACC');

    // Access index with approval_status=Approved
    $approvedResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.index', ['approval_status' => 'Approved']));

    $approvedResponse->assertStatus(200);
    $approvedResponse->assertSee('Inisiatif Ad Hoc Sudah ACC');
    $approvedResponse->assertDontSee('Inisiatif Ad Hoc Menunggu ACC');
});

