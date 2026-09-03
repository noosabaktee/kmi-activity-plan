<?php

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyPlanActivity;
use App\Models\TrDailyTask;
use App\Models\TrSubProject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('it renders the login page with system branding and summary stats', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('KMI Activity Plan');
    $response->assertSee('MDP Department Monitoring');
});

test('it allows Head, Supervisor, and Employee to login with credentials', function () {
    $response = $this->post(route('login.authenticate'), [
        'txtEmail' => 'head.mdp@kalbe.co.id',
        'txtPassword' => '123456',
    ]);

    $response->assertRedirect(route('dashboard.index'));
    $this->assertEquals(session('auth_user_id'), MUser::where('txtEmail', 'head.mdp@kalbe.co.id')->value('intUser_ID'));
});

test('it displays dashboard with KPI metrics, sub-department cards and S-Curve preview', function () {
    $user = MUser::where('txtRole', 'Head')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('dashboard.index'));

    $response->assertStatus(200);
    $response->assertSee('DASHBOARD KPI & ACTIVITY');
    $response->assertSee('MD/IT');
    $response->assertSee('MO/PPIC');
    $response->assertSee('AM');
    $response->assertSee('MP/Project');
});

test('it displays project catalog with single and multi sub projects', function () {
    $user = MUser::where('txtRole', 'Head')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('projects.index'));

    $response->assertStatus(200);
    $response->assertSee('AI Agent');
    $response->assertSee('Sub Projects');
});

test('it creates a new single project with stages', function () {
    $user = MUser::where('txtRole', 'Head')->first();
    $subDept = MSubDepartment::first();
    $type = MProjectType::first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('projects.store'), [
            'bitHasSubProject' => '0',
            'txtProjectName' => 'New Automated Testing System',
            'txtProjectCode' => 'PRJ-TEST-01',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intUser_ID' => $user->intUser_ID,
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 15,
            'txtDeliverable' => 'Full test suite',
            'txtTargetSkalaGrade' => "1. 80%\n2. 90%\n3. 100%",
            'stages' => [
                ['step' => 'Planning', 'start' => '2026-01-01', 'end' => '2026-03-31', 'plan' => 50, 'actual' => 50],
                ['step' => 'Deployment', 'start' => '2026-04-01', 'end' => '2026-06-30', 'plan' => 50, 'actual' => 20],
            ],
        ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('mProject', [
        'txtProjectName' => 'New Automated Testing System',
        'floatWeight' => 15,
    ]);
});

test('it creates a project with sub projects containing dates and direct stages', function () {
    $user = MUser::where('txtRole', 'Head')->first();
    $subDept = MSubDepartment::first();
    $type = MProjectType::first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('projects.store'), [
            'bitHasSubProject' => '1',
            'txtProjectName' => 'AI Multi-Agent Ecosystem',
            'txtProjectCode' => 'PRJ-AI-001',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intUser_ID' => $user->intUser_ID,
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 25,
            'dtmProjectStartDate' => '2026-01-01',
            'dtmProjectEndDate' => '2026-12-31',
            'sub_projects' => [
                [
                    'name' => 'Agent Sub 1',
                    'weight' => 50,
                    'start_date' => '2026-01-15',
                    'end_date' => '2026-06-30',
                    'deliverable' => 'Core Bot',
                    'stages' => [
                        ['step' => 'Planning Sub 1', 'start' => '2026-01-15', 'end' => '2026-03-31', 'plan' => 50, 'actual' => 50],
                        ['step' => 'Execution Sub 1', 'start' => '2026-04-01', 'end' => '2026-06-30', 'plan' => 50, 'actual' => 25],
                    ],
                ],
                [
                    'name' => 'Agent Sub 2',
                    'weight' => 50,
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-12-20',
                    'deliverable' => 'Web App Bot',
                    'stages' => [
                        ['step' => 'Execution Sub 2', 'start' => '2026-07-01', 'end' => '2026-12-20', 'plan' => 100, 'actual' => 50],
                    ],
                ],
            ],
        ]);

    $response->assertRedirect(route('projects.index'));

    $project = MProject::where('txtProjectName', 'AI Multi-Agent Ecosystem')->first();
    expect($project)->not->toBeNull();
    expect($project->bitHasSubProject)->toBeTrue();

    $this->assertDatabaseHas('trSubProject', [
        'intProject_ID' => $project->intProject_ID,
        'txtSubProjectName' => 'Agent Sub 1',
        'floatWeight' => 50,
        'dtmStartDate' => '2026-01-15 00:00:00',
        'dtmEndDate' => '2026-06-30 00:00:00',
    ]);

    $sub1 = TrSubProject::where('intProject_ID', $project->intProject_ID)->where('txtSubProjectName', 'Agent Sub 1')->first();
    expect($sub1->stages)->toHaveCount(2);
    expect($sub1->floatProgress)->toBe(75.0); // (50 + 25) / 100 * 100 = 75%

    $this->assertDatabaseHas('trProjectStage', [
        'intProject_ID' => $project->intProject_ID,
        'intSubProject_ID' => $sub1->intSubProject_ID,
        'txtProjectStageStep' => 'Planning Sub 1',
        'dtmProjectStageStartDate' => '2026-01-15 00:00:00',
    ]);
});

test('it updates an existing project with sub projects, updating dates and stages', function () {
    $user = MUser::where('txtRole', 'Head')->first();
    $subDept = MSubDepartment::first();
    $type = MProjectType::first();

    $project = MProject::create([
        'intDepartment_ID' => 1,
        'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
        'intProjectType_ID' => $type->intProjectType_ID,
        'intUser_ID' => $user->intUser_ID,
        'txtProjectCode' => 'PRJ-UPDATE-001',
        'txtProjectName' => 'Initial Project To Update',
        'txtKpiLevel' => 'Individu',
        'floatWeight' => 30,
        'bitHasSubProject' => true,
        'bitActive' => true,
        'txtInsertedBy' => 'system',
        'dtmInserted' => now(),
    ]);

    $sub = TrSubProject::create([
        'intProject_ID' => $project->intProject_ID,
        'txtSubProjectName' => 'Sub Alpha',
        'floatWeight' => 100,
        'txtStatus' => 'In Progress',
        'txtInsertedBy' => 'system',
        'dtmInserted' => now(),
    ]);

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->put(route('projects.update', $project), [
            'bitHasSubProject' => '1',
            'txtProjectName' => 'Updated Project Name',
            'txtProjectCode' => 'PRJ-UPDATE-001',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intUser_ID' => $user->intUser_ID,
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 30,
            'sub_projects' => [
                [
                    'id' => $sub->intSubProject_ID,
                    'name' => 'Sub Alpha Updated',
                    'weight' => 60,
                    'score' => 5,
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-08-31',
                    'stages' => [
                        ['step' => 'Sprint 1', 'start' => '2026-02-01', 'end' => '2026-04-30', 'plan' => 50, 'actual' => 50],
                        ['step' => 'Sprint 2', 'start' => '2026-05-01', 'end' => '2026-08-31', 'plan' => 50, 'actual' => 50],
                    ],
                ],
                [
                    'name' => 'Sub Beta Brand New',
                    'weight' => 40,
                    'score' => 4,
                    'start_date' => '2026-09-01',
                    'end_date' => '2026-12-31',
                    'stages' => [
                        ['step' => 'Rollout', 'start' => '2026-09-01', 'end' => '2026-12-31', 'plan' => 100, 'actual' => 20],
                    ],
                ],
            ],
        ]);

    $response->assertRedirect(route('projects.show', $project));

    $this->assertDatabaseHas('trSubProject', [
        'intSubProject_ID' => $sub->intSubProject_ID,
        'txtSubProjectName' => 'Sub Alpha Updated',
        'floatWeight' => 60,
        'dtmStartDate' => '2026-02-01 00:00:00',
        'dtmEndDate' => '2026-08-31 00:00:00',
    ]);

    $this->assertDatabaseHas('trSubProject', [
        'intProject_ID' => $project->intProject_ID,
        'txtSubProjectName' => 'Sub Beta Brand New',
        'floatWeight' => 40,
    ]);

    $sub->refresh();
    expect($sub->floatProgress)->toBe(100.0);
});

test('it calculates exposure curve points for API and dashboard', function () {
    $response = $this->get('/api/v1/exposure');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'projects',
        'employees',
        'projectTypes',
    ]);
});

test('it renders daily task spreadsheet page', function () {
    $user = MUser::where('txtRole', 'Employee')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('reports.daily-tasks'));

    $response->assertStatus(200);
    $response->assertSee('DAILY TASK SPREADSHEET');
    $response->assertSee('hotTableContainer');
});

test('it handles daily task spreadsheet batch save and excel export', function () {
    $user = MUser::where('txtRole', 'Employee')->first();
    $project = MProject::first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->postJson(route('reports.daily-tasks.batch-save'), [
            'rows' => [
                [
                    'date' => '2026-09-01',
                    'employeeId' => $user->intUser_ID,
                    'projectId' => $project->intProject_ID,
                    'subProjectId' => null,
                    'activity' => 'Testing Handsontable Sync',
                    'deliverable' => 'Verified sync',
                    'duration' => 3.5,
                    'progress' => 100,
                    'status' => 'Completed',
                    'notes' => 'Unit test log',
                ],
            ],
        ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('trDailyTask', [
        'txtActivityDescription' => 'Testing Handsontable Sync',
        'floatDurationHours' => 3.5,
    ]);

    $exportResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('reports.daily-tasks.export-excel'));

    $exportResponse->assertStatus(200);
});

test('it manages weekly plan cards and daily activities from monday to friday', function () {
    $user = MUser::where('txtRole', 'Employee')->first();

    $plan = MWeeklyPlan::create([
        'intUser_ID' => $user->intUser_ID,
        'txtWeekTitle' => 'Week Test: 31 Aug - 04 Sep 2026',
        'dtmWeekStartDate' => '2026-08-31',
        'dtmWeekEndDate' => '2026-09-04',
        'txtTargetGoals' => 'Target 100%',
        'txtStatus' => 'Draft',
        'txtInsertedBy' => $user->txtEmployeeName,
    ]);

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('daily-plans.show', $plan));

    $response->assertStatus(200);
    $response->assertSee('Senin');
    $response->assertSee('Jumat');

    // Add activity to Senin
    $actResponse = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('reports.daily-plans.activities.store', $plan), [
            'txtDayName' => 'Senin',
            'dtmActivityDate' => '2026-08-31',
            'txtActivityName' => 'Check Machine Ampere Sensor',
            'txtStartTime' => '08:00',
            'txtEndTime' => '10:00',
            'floatDuration' => 2.0,
            'txtLocationType' => 'SENTUL',
            'txtRemarks' => 'Normal condition',
        ]);

    $actResponse->assertRedirect(route('daily-plans.show', $plan));
    $this->assertDatabaseHas('trDailyPlanActivity', [
        'txtActivityName' => 'Check Machine Ampere Sensor',
        'txtLocationType' => 'SENTUL',
    ]);
});

test('it only displays projects belonging to the employee in daily plan activity project select', function () {
    $employees = MUser::where('txtRole', 'Employee')->take(2)->get();
    $emp1 = $employees[0];
    $emp2 = $employees[1];

    $type = MProjectType::first();

    $project1 = MProject::create([
        'intDepartment_ID' => 1,
        'intSubDepartment_ID' => $emp1->intSubDepartment_ID ?: 1,
        'intProjectType_ID' => $type->intProjectType_ID,
        'intUser_ID' => $emp1->intUser_ID,
        'txtProjectCode' => 'PRJ-EMP1-01',
        'txtProjectName' => 'Exclusive Project For Emp 1',
        'txtKpiLevel' => 'Individu',
        'floatWeight' => 10,
        'bitHasSubProject' => false,
        'bitActive' => true,
    ]);

    $project2 = MProject::create([
        'intDepartment_ID' => 1,
        'intSubDepartment_ID' => $emp2->intSubDepartment_ID ?: 1,
        'intProjectType_ID' => $type->intProjectType_ID,
        'intUser_ID' => $emp2->intUser_ID,
        'txtProjectCode' => 'PRJ-EMP2-01',
        'txtProjectName' => 'Exclusive Project For Emp 2',
        'txtKpiLevel' => 'Individu',
        'floatWeight' => 10,
        'bitHasSubProject' => false,
        'bitActive' => true,
    ]);

    $planEmp1 = MWeeklyPlan::create([
        'intUser_ID' => $emp1->intUser_ID,
        'txtWeekTitle' => 'Week Plan Emp 1',
        'dtmWeekStartDate' => '2026-09-07',
        'dtmWeekEndDate' => '2026-09-11',
        'txtStatus' => 'Draft',
    ]);

    $response = $this->withSession(['auth_user_id' => $emp1->intUser_ID])
        ->get(route('daily-plans.show', $planEmp1));

    $response->assertStatus(200);
    $response->assertViewHas('projects', function ($projects) use ($project1, $project2) {
        return $projects->contains('intProject_ID', $project1->intProject_ID)
            && ! $projects->contains('intProject_ID', $project2->intProject_ID);
    });
    $response->assertSee('Exclusive Project For Emp 1');
    $response->assertDontSee('Exclusive Project For Emp 2');
});

test('it renders monthly report progress dashboard with interactive charts and tracker', function () {
    $user = MUser::where('txtRole', 'Head')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('reports.monthly-report'));

    $response->assertStatus(200);
    $response->assertSee('Laporan Perkembangan & Progress', false);
    $response->assertSee('projectProgressChart');
    $response->assertSee('projectStatusChart');
    $response->assertSee('dailyTrendChart');
    $response->assertSee('subDeptProgressChart');
    $response->assertSee('Tabel Ringkasan Perkembangan Project');
});

test('it loads supervisedSubDepartments relationship without query exception', function () {
    $spv = MUser::where('txtRole', 'Supervisor')->first();

    // SPV accessing profile
    $responseSpv = $this->withSession(['auth_user_id' => $spv->intUser_ID])
        ->get(route('profile.show'));
    $responseSpv->assertStatus(200);

    // Head accessing master data
    $head = MUser::where('txtRole', 'Head')->first();
    $responseHead = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->get(route('master.index', ['tab' => 'users']));
    $responseHead->assertStatus(200);

    // Verify relationship query executes properly
    $loadedSpv = MUser::with('supervisedSubDepartments')->find($spv->intUser_ID);
    expect($loadedSpv->supervisedSubDepartments)->not->toBeNull();
});

test('it creates a new supervisor user with supervised sub departments', function () {
    $head = MUser::where('txtRole', 'Head')->first();
    $subDept1 = MSubDepartment::find(1);
    $subDept2 = MSubDepartment::find(2);

    $response = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->post(route('master.users.store'), [
            'txtEmployeeName' => 'Supervisor Test',
            'txtEmployeeCode' => 'SPV-TST',
            'txtEmail' => 'spv.test@kalbe.co.id',
            'txtPhone' => '081234567899',
            'txtRole' => 'Supervisor',
            'intDepartment_ID' => 1,
            'intSubDepartment_ID' => 1,
            'txtPassword' => 'password123',
            'supervised_subdepts' => [$subDept1->intSubDepartment_ID, $subDept2->intSubDepartment_ID],
        ]);

    $response->assertRedirect(route('master.index', ['tab' => 'users']));

    $createdUser = MUser::where('txtEmail', 'spv.test@kalbe.co.id')->first();
    expect($createdUser)->not->toBeNull();
    expect($createdUser->supervisedSubDepartments)->toHaveCount(2);

    $this->assertDatabaseHas('trSupervisorSubDept', [
        'intUser_ID' => $createdUser->intUser_ID,
        'intSubDepartment_ID' => $subDept1->intSubDepartment_ID,
    ]);
});

test('it displays comprehensive employee profile summary with exposure, projects, and activities', function () {
    $emp = MUser::where('txtRole', 'Employee')->first();

    $response = $this->withSession(['auth_user_id' => $emp->intUser_ID])
        ->get(route('profile.show'));

    $response->assertStatus(200);
    $response->assertSee($emp->txtEmployeeName);
    $response->assertSee('Kurva Exposure S-Curve Karyawan');
    $response->assertSee('Portofolio Project KPI');
    $response->assertSee('Log Daily Tasks Terakhir');
    $response->assertSee('Rencana Kerja Mingguan');
    $response->assertSee('profileExposureChart');
});

test('it creates a project without project code and auto-generates sequential code', function () {
    $user = MUser::where('txtRole', 'Head')->first();
    $subDept = MSubDepartment::first();
    $type = MProjectType::first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('projects.store'), [
            'bitHasSubProject' => '0',
            'txtProjectName' => 'Project Auto Code Generation Test',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intUser_ID' => $user->intUser_ID,
            'txtKpiLevel' => 'Department',
            'floatWeight' => 20,
        ]);

    $response->assertRedirect(route('projects.index'));

    $project = MProject::where('txtProjectName', 'Project Auto Code Generation Test')->first();
    expect($project)->not->toBeNull();
    expect($project->txtProjectCode)->toStartWith('PRJ-' . date('Y'));
});

test('it manages WA scheduler with day select, two-tier target, and API settings update', function () {
    $superadmin = MUser::where('txtRole', 'Superadmin')->first() ?: MUser::where('txtRole', 'Head')->first();
    $dept = MDepartment::first();

    // 1. Test update WA API settings
    $settingsResponse = $this->withSession(['auth_user_id' => $superadmin->intUser_ID])
        ->post(route('wa-scheduler.settings.update'), [
            'wa_api_url' => 'https://whatsapp.intconnect.id/send-message',
            'wa_api_key' => 'test_api_key_123456',
            'wa_sender' => '628999888777',
            'wa_footer' => 'Sent from KMI Activity Plan Test',
        ]);

    $settingsResponse->assertRedirect(route('wa-scheduler.index'));
    $this->assertEquals('test_api_key_123456', \App\Models\MSetting::get('wa_api_key'));
    $this->assertEquals('628999888777', \App\Models\MSetting::get('wa_sender'));

    // 2. Test create WA schedule with day name & department & role target
    $schedResponse = $this->withSession(['auth_user_id' => $superadmin->intUser_ID])
        ->post(route('wa-scheduler.store'), [
            'txtScheduleTitle' => 'Pengingat Jumat Sore Test',
            'txtCronDay' => 'Jumat',
            'txtScheduledTime' => '16:00',
            'intDepartment_ID' => $dept->intDepartment_ID,
            'txtTargetRecipient' => 'Employee',
            'txtMessageTemplate' => 'Halo {employee_name}, selamat sore!',
        ]);

    $schedResponse->assertRedirect(route('wa-scheduler.index'));

    $this->assertDatabaseHas('mWaSchedule', [
        'txtScheduleTitle' => 'Pengingat Jumat Sore Test',
        'txtCronDay' => 'Jumat',
        'txtTargetRole' => 'Employee',
        'intDepartment_ID' => $dept->intDepartment_ID,
    ]);
});
