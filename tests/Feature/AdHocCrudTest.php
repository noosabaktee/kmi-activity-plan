<?php

use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyTask;
use App\Models\TrProjectAssignment;
use App\Models\TrProjectStage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

function createSampleAdHoc(MUser $user, array $overrides = []): MProject
{
    $subDept = MSubDepartment::first();

    $adhoc = MProject::create(array_merge([
        'intDepartment_ID' => $user->intDepartment_ID ?: 1,
        'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
        'intProjectType_ID' => 5,
        'intUser_ID' => $user->intUser_ID,
        'txtProjectCode' => 'ADH-2026-001',
        'txtProjectName' => 'Penanganan Error PLC Sachet-A Line 1',
        'txtKpiLevel' => 'Ad Hoc',
        'txtDeliverable' => 'Motor stabil tanpa trip',
        'txtTargetSkalaGrade' => '1. Mesin normal',
        'floatWeight' => 10.0,
        'bitHasSubProject' => false,
        'bitIsAdHoc' => true,
        'txtAdHocCategory' => 'Troubleshooting & Problem Solving',
        'txtPriority' => 'Critical',
        'txtSpecialGoal' => 'Menghentikan alarm overload motor line 1.',
        'txtDescription' => 'Terjadi lonjakan arus sejak shift 2.',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(10),
        'floatPlan' => 100,
        'floatActual' => 30,
        'txtStatus' => 'In Progress',
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
        'bitActive' => true,
    ], $overrides));

    TrProjectAssignment::create([
        'intProject_ID' => $adhoc->intProject_ID,
        'intUser_ID' => $user->intUser_ID,
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
    ]);

    TrProjectStage::create([
        'intProject_ID' => $adhoc->intProject_ID,
        'intProjectStageNumber' => 1,
        'txtProjectStageStep' => 'Investigasi Sumber Arus & Wiring',
        'dtmProjectStageStartDate' => now(),
        'dtmProjectStageEndDate' => now()->addDays(2),
        'floatProjectStagePlan' => 50,
        'floatProjectStageActual' => 30,
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
    ]);

    return $adhoc;
}

test('authenticated user can access ad hoc index page and sees summary metrics', function () {
    $user = MUser::where('txtRole', 'Employee')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.index'));

    $response->assertStatus(200);
    $response->assertSee('AD HOC INITIATIVE MANAGEMENT');
    $response->assertSee('Total Ad Hoc');
    $response->assertSee('Sedang Ditangani');
    $response->assertSee('Selesai Ditangani');
    $response->assertSee('Kritis / Urgent');
});

test('authenticated user can access ad hoc create page with customized fields', function () {
    $user = MUser::where('txtRole', 'Employee')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.create'));

    $response->assertStatus(200);
    $response->assertSee('BUAT INISIATIF AD HOC BARU');
    $response->assertSee('Kategori Ad Hoc');
    $response->assertSee('Tingkat Urgensi / Prioritas');
    $response->assertSee('Sasaran / Tujuan Tunggal yang Ingin Dicapai');
    $response->assertSee('Sifat Sementara');
    $response->assertSee('Penugasan Tim Pelaksana');
    $response->assertSee('Rencana Tahapan Aksi');
});

test('user can store a new ad hoc initiative with action stages and team members', function () {
    $user = MUser::where('txtRole', 'Employee')->first();
    $subDept = MSubDepartment::first();
    $colleague = MUser::where('intUser_ID', '!=', $user->intUser_ID)->first();

    $payload = [
        'txtProjectName' => 'Penanganan Error PLC Sachet-A Line 1',
        'txtAdHocCategory' => 'Troubleshooting & Problem Solving',
        'txtPriority' => 'Critical',
        'txtSpecialGoal' => 'Menghentikan alarm overload dan menstabilkan arus ampere motor line 1.',
        'txtDescription' => 'Terjadi lonjakan arus sejak shift 2, membutuhkan investigasi dan penanganan segera.',
        'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
        'intUser_ID' => $user->intUser_ID,
        'assignments' => [$user->intUser_ID, $colleague->intUser_ID],
        'dtmProjectStartDate' => date('Y-m-d'),
        'dtmProjectEndDate' => date('Y-m-d', strtotime('+10 days')),
        'txtDeliverable' => 'Motor beroperasi stabil tanpa trip & laporan RCA',
        'txtTargetSkalaGrade' => "1. Mesin normal\n2. Zero downtime 7 hari",
        'floatWeight' => 15.0,
        'txtStatus' => 'In Progress',
        'stages' => [
            [
                'step' => 'Investigasi Sumber Arus & Wiring',
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d', strtotime('+2 days')),
                'plan' => 30,
                'actual' => 30,
            ],
            [
                'step' => 'Penggantian Modul & Setting Inverter',
                'start' => date('Y-m-d', strtotime('+3 days')),
                'end' => date('Y-m-d', strtotime('+6 days')),
                'plan' => 50,
                'actual' => 25,
            ],
            [
                'step' => 'Running Test 24 Jam & Dokumentasi',
                'start' => date('Y-m-d', strtotime('+7 days')),
                'end' => date('Y-m-d', strtotime('+10 days')),
                'plan' => 20,
                'actual' => 0,
            ],
        ],
    ];

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('adhocs.store'), $payload);

    $response->assertRedirect(route('adhocs.index'));
    $response->assertSessionHas('success');

    $adhoc = MProject::where('txtProjectName', 'Penanganan Error PLC Sachet-A Line 1')->first();
    expect($adhoc)->not->toBeNull();
    expect($adhoc->bitIsAdHoc)->toBeTrue();
    expect($adhoc->intProjectType_ID)->toBe(5);
    expect($adhoc->txtProjectCode)->toMatch('/^ADH-\d{4}-\d{3}$/');
    expect($adhoc->txtPriority)->toBe('Critical');
    expect($adhoc->txtAdHocCategory)->toBe('Troubleshooting & Problem Solving');

    // Verify stages created
    $stages = TrProjectStage::where('intProject_ID', $adhoc->intProject_ID)->get();
    expect($stages)->toHaveCount(3);

    // Verify assignments created
    $assignments = TrProjectAssignment::where('intProject_ID', $adhoc->intProject_ID)->get();
    expect($assignments)->toHaveCount(2);

    // Verify progress recalculated: (30 + 25 + 0) / (30 + 50 + 20) * 100 = 55%
    expect($adhoc->floatActual)->toBe(55.0);
});

test('it separates ad hoc initiatives from standard projects catalog', function () {
    $user = MUser::where('txtRole', 'Superadmin')->first();
    createSampleAdHoc($user);

    // Verify standard projects catalog excludes ad hoc
    $projectsResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('projects.index'));

    $projectsResponse->assertStatus(200);
    $projectsResponse->assertDontSee('Penanganan Error PLC Sachet-A Line 1');

    // Verify ad hoc catalog includes it
    $adhocsResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.index'));

    $adhocsResponse->assertStatus(200);
    $adhocsResponse->assertSee('Penanganan Error PLC Sachet-A Line 1');
    $adhocsResponse->assertSee('Troubleshooting & Problem Solving');
});

test('user can view ad hoc detail, update it, and deactive/delete it', function () {
    $user = MUser::where('txtRole', 'Employee')->first();
    $adhoc = createSampleAdHoc($user);

    // 1. Show page
    $showResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.show', $adhoc));

    $showResponse->assertStatus(200);
    $showResponse->assertSee('DETAIL INISIATIF AD HOC');
    $showResponse->assertSee($adhoc->txtProjectName);
    $showResponse->assertSee('Investigasi Sumber Arus & Wiring');

    // 2. Edit page
    $editResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.edit', $adhoc));

    $editResponse->assertStatus(200);
    $editResponse->assertSee('Formulir Pembaruan Ad Hoc');

    // 3. Update
    $updatePayload = [
        'txtProjectName' => 'Penanganan Error PLC Sachet-A Line 1 (Updated)',
        'txtAdHocCategory' => 'Troubleshooting & Problem Solving',
        'txtPriority' => 'High',
        'txtSpecialGoal' => 'Tujuan khusus berhasil diselesaikan dengan baik.',
        'txtDescription' => 'Sudah selesai penanganan inverter.',
        'intSubDepartment_ID' => $adhoc->intSubDepartment_ID,
        'intUser_ID' => $user->intUser_ID,
        'assignments' => [$user->intUser_ID],
        'dtmProjectStartDate' => $adhoc->dtmProjectStartDate->format('Y-m-d'),
        'dtmProjectEndDate' => $adhoc->dtmProjectEndDate->format('Y-m-d'),
        'txtDeliverable' => 'Output deliverable final',
        'txtStatus' => 'Completed',
        'stages' => [
            [
                'step' => 'Langkah Final Diselesaikan',
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d'),
                'plan' => 100,
                'actual' => 100,
            ],
        ],
    ];

    $updateResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->put(route('adhocs.update', $adhoc), $updatePayload);

    $updateResponse->assertRedirect(route('adhocs.index'));
    $adhoc->refresh();
    expect($adhoc->txtProjectName)->toBe('Penanganan Error PLC Sachet-A Line 1 (Updated)');
    expect($adhoc->txtPriority)->toBe('High');
    expect($adhoc->txtStatus)->toBe('Completed');
    expect($adhoc->floatActual)->toBe(100.0);

    // 4. Destroy
    $deleteResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->delete(route('adhocs.destroy', $adhoc));

    $deleteResponse->assertRedirect(route('adhocs.index'));
    $adhoc->refresh();
    expect($adhoc->bitActive)->toBeFalse();
});

test('ad hoc seamlessly integrates into daily tasks and daily plan reports', function () {
    $user = MUser::where('txtRole', 'Employee')->first();

    // Create an active ad hoc for reporting test
    $subDept = MSubDepartment::first();
    $adhoc = MProject::create([
        'intDepartment_ID' => $user->intDepartment_ID ?: 1,
        'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
        'intProjectType_ID' => 5,
        'intUser_ID' => $user->intUser_ID,
        'txtProjectCode' => 'ADH-2026-099',
        'txtProjectName' => 'Inspeksi Ad Hoc Suhu Panel Trafo',
        'txtKpiLevel' => 'Ad Hoc',
        'txtDeliverable' => 'Laporan inspeksi thermovisi',
        'bitHasSubProject' => false,
        'bitIsAdHoc' => true,
        'txtAdHocCategory' => 'Emergency Response',
        'txtPriority' => 'High',
        'txtSpecialGoal' => 'Cek titik panas pada panel trafo 2.',
        'dtmProjectStartDate' => now(),
        'dtmProjectEndDate' => now()->addDays(3),
        'floatPlan' => 100,
        'floatActual' => 0,
        'txtStatus' => 'In Progress',
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
        'bitActive' => true,
    ]);

    TrProjectAssignment::create([
        'intProject_ID' => $adhoc->intProject_ID,
        'intUser_ID' => $user->intUser_ID,
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
    ]);

    $stage = TrProjectStage::create([
        'intProject_ID' => $adhoc->intProject_ID,
        'intProjectStageNumber' => 1,
        'txtProjectStageStep' => 'Pengukuran Thermovisi Panel',
        'dtmProjectStageStartDate' => now(),
        'dtmProjectStageEndDate' => now()->addDays(1),
        'floatProjectStagePlan' => 100,
        'floatProjectStageActual' => 0,
        'txtInsertedBy' => 'test',
        'dtmInserted' => now(),
    ]);

    // 1. Batch Save Daily Task on this Ad Hoc
    $batchPayload = [
        'rows' => [
            [
                'date' => now()->toDateString(),
                'employeeId' => $user->intUser_ID,
                'projectTypeId' => 5,
                'projectId' => $adhoc->intProject_ID,
                'stageId' => $stage->intProjectStage_ID,
                'activity' => 'Melakukan pemindaian suhu thermal kamera flir pada busbar trafo',
                'deliverable' => 'Hasil foto thermal & log suhu',
                'duration' => 2.5,
                'progress' => 100,
                'status' => 'Completed',
                'notes' => 'Suhu normal di bawah 55 derajat Celcius',
            ],
        ],
    ];

    $saveResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->postJson(route('reports.daily-tasks.batch-save'), $batchPayload);

    $saveResponse->assertStatus(200);
    $saveResponse->assertJson(['success' => true]);

    $dailyTask = TrDailyTask::where('intProject_ID', $adhoc->intProject_ID)->first();
    expect($dailyTask)->not->toBeNull();
    expect($dailyTask->txtActivityDescription)->toContain('pemindaian suhu thermal');

    // 2. Ad Hoc show page displays this logged task
    $showResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('adhocs.show', $adhoc));
    $showResponse->assertSee('Melakukan pemindaian suhu thermal kamera flir pada busbar trafo');
    $showResponse->assertSee('2.5 jam');

    // 3. Daily plan detail displays ad hoc with [Ad Hoc] label
    $weeklyPlan = MWeeklyPlan::where('intUser_ID', $user->intUser_ID)->first();
    if ($weeklyPlan) {
        $planDetailResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
            ->get(route('daily-plans.show', $weeklyPlan));

        $planDetailResponse->assertStatus(200);
        $planDetailResponse->assertSee('[Ad Hoc] Inspeksi Ad Hoc Suhu Panel Trafo');
    }
});
