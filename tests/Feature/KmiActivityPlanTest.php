<?php

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSkillset;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyPlanActivity;
use App\Models\TrDailyTask;
use App\Models\TrProjectAssignment;
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
        'txtEmail' => 'nrs@kalbe.co.id',
        'txtPassword' => '123456',
    ]);

    $response->assertRedirect(route('dashboard.index'));
    $this->assertEquals(session('auth_user_id'), MUser::where('txtEmail', 'nrs@kalbe.co.id')->value('intUser_ID'));
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

test('it renders the project creation and edit form successfully', function () {
    $user = MUser::where('txtRole', 'Head')->first();
    $project = MProject::first();

    $responseCreate = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('projects.create'));
    $responseCreate->assertStatus(200);
    $responseCreate->assertSee('Formulir Rencana Project');
    $responseCreate->assertSee('Assignment Karyawan (Pelaksana Project)');

    $responseEdit = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('projects.edit', $project));
    $responseEdit->assertStatus(200);
    $responseEdit->assertSee('Edit Project');
    $responseEdit->assertSee('Assignment Karyawan (Pelaksana Project)');
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
            'assignments' => [$user->intUser_ID, 9],
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

    $singleProject = MProject::where('txtProjectName', 'New Automated Testing System')->first();
    $this->assertDatabaseHas('trProjectAssignment', [
        'intProject_ID' => $singleProject->intProject_ID,
        'intSubProject_ID' => null,
        'intUser_ID' => 9,
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
                    'assignments' => [5, 9],
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
                    'assignments' => [10],
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
    $this->assertDatabaseHas('trProjectAssignment', [
        'intProject_ID' => $project->intProject_ID,
        'intSubProject_ID' => $sub1->intSubProject_ID,
        'intUser_ID' => 5,
    ]);
    expect($sub1->stages)->toHaveCount(2);
    expect($sub1->floatProgress)->toBe(75.0); // (50 + 25) / 100 * 100 = 75%

    $this->assertDatabaseHas('trProjectStage', [
        'intProject_ID' => $project->intProject_ID,
        'intSubProject_ID' => $sub1->intSubProject_ID,
        'txtProjectStageStep' => 'Planning Sub 1',
        'dtmProjectStageStartDate' => '2026-01-15 00:00:00',
    ]);
});

test('it manages multiple employee assignments for single and sub-projects with ownership scope', function () {
    $emp1 = MUser::find(5); // NRS
    $emp2 = MUser::find(9); // AHO
    $subDept = MSubDepartment::first();
    $type = MProjectType::first();

    // 1. Single Project with Multiple Assignments
    $response = $this->withSession(['auth_user_id' => $emp1->intUser_ID])
        ->post(route('projects.store'), [
            'bitHasSubProject' => '0',
            'txtProjectName' => 'Collaborative Single Project',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'assignments' => [$emp1->intUser_ID, $emp2->intUser_ID],
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 20,
            'stages' => [
                ['step' => 'Stage 1', 'start' => '2026-01-01', 'end' => '2026-06-30', 'plan' => 100, 'actual' => 50],
            ],
        ]);

    $response->assertRedirect(route('projects.index'));
    $singleProj = MProject::where('txtProjectName', 'Collaborative Single Project')->first();
    expect($singleProj)->not->toBeNull();
    expect($singleProj->allAssignedUsers()->pluck('intUser_ID')->toArray())->toEqualCanonicalizing([$emp1->intUser_ID, $emp2->intUser_ID]);

    // Both employees can query this project via forUser scope
    expect(MProject::forUser($emp1->intUser_ID)->pluck('intProject_ID'))->toContain($singleProj->intProject_ID);
    expect(MProject::forUser($emp2->intUser_ID)->pluck('intProject_ID'))->toContain($singleProj->intProject_ID);

    // 2. Sub Project with Distinct Assignments
    $emp3 = MUser::find(10); // AMI
    $response2 = $this->withSession(['auth_user_id' => $emp1->intUser_ID])
        ->post(route('projects.store'), [
            'bitHasSubProject' => '1',
            'txtProjectName' => 'Multi Sub Assignment Project',
            'intProjectType_ID' => $type->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'txtKpiLevel' => 'Department',
            'floatWeight' => 30,
            'sub_projects' => [
                [
                    'name' => 'Sub Alpha',
                    'weight' => 50,
                    'assignments' => [$emp2->intUser_ID],
                    'stages' => [
                        ['step' => 'Step A', 'start' => '2026-01-01', 'end' => '2026-06-30', 'plan' => 100, 'actual' => 100],
                    ],
                ],
                [
                    'name' => 'Sub Beta',
                    'weight' => 50,
                    'assignments' => [$emp3->intUser_ID],
                    'stages' => [
                        ['step' => 'Step B', 'start' => '2026-07-01', 'end' => '2026-12-31', 'plan' => 100, 'actual' => 50],
                    ],
                ],
            ],
        ]);

    $response2->assertRedirect(route('projects.index'));
    $multiProj = MProject::where('txtProjectName', 'Multi Sub Assignment Project')->first();
    expect($multiProj)->not->toBeNull();

    // Emp2 and Emp3 own the project via their respective sub-projects
    expect(MProject::forUser($emp2->intUser_ID)->pluck('intProject_ID'))->toContain($multiProj->intProject_ID);
    expect(MProject::forUser($emp3->intUser_ID)->pluck('intProject_ID'))->toContain($multiProj->intProject_ID);
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

test('it displays skillsets in master data and allows adding and updating a skillset', function () {
    $head = MUser::where('txtRole', 'Head')->first();

    // 1. Visit master data skillsets tab
    $response = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->get(route('master.index', ['tab' => 'skillsets']));

    $response->assertStatus(200);
    $response->assertSee('Web Development');
    $response->assertSee('AI & Computer Vision');
    $response->assertSee('Embedded Systems & IoT Data Acquisition');

    // 2. Add new skillset
    $storeResponse = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->post(route('master.skillsets.store'), [
            'txtSkillsetName' => 'Quantum Computing & Simulation',
            'txtDescription' => 'Riset komputasi kuantum untuk optimasi manufaktur.',
            'txtBadgeColor' => '#4C1D95',
            'txtIcon' => 'fa-solid fa-atom',
        ]);

    $storeResponse->assertRedirect(route('master.index', ['tab' => 'skillsets']));
    $this->assertDatabaseHas('mSkillset', [
        'txtSkillsetName' => 'Quantum Computing & Simulation',
        'txtBadgeColor' => '#4C1D95',
    ]);

    // 3. Update skillset
    $created = MSkillset::where('txtSkillsetName', 'Quantum Computing & Simulation')->first();
    $updateResponse = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->put(route('master.skillsets.update', $created), [
            'txtSkillsetName' => 'Advanced Quantum Computing',
            'txtDescription' => 'Updated deskripsi komputasi kuantum.',
            'txtBadgeColor' => '#581C87',
            'txtIcon' => 'fa-solid fa-atom',
        ]);

    $updateResponse->assertRedirect(route('master.index', ['tab' => 'skillsets']));
    $this->assertDatabaseHas('mSkillset', [
        'intSkillset_ID' => $created->intSkillset_ID,
        'txtSkillsetName' => 'Advanced Quantum Computing',
    ]);
});

test('it allows creating and editing a main project with a skillset from master data', function () {
    $head = MUser::where('txtRole', 'Head')->first();
    $projectType = MProjectType::first();
    $subDept = MSubDepartment::first();
    $skillset = MSkillset::where('txtSkillsetName', 'Web Development')->first();

    // 1. Open create page and see skillset
    $createPage = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->get(route('projects.create'));

    $createPage->assertStatus(200);
    $createPage->assertSee('Skillset Utama Project');
    $createPage->assertSee('Web Development');

    // 2. Store project with skillset
    $storeResponse = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->post(route('projects.store'), [
            'txtProjectName' => 'Project Portal Web Kalbe Test',
            'txtProjectCode' => 'PRJ-TEST-WEB-001',
            'intProjectType_ID' => $projectType->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intSkillset_ID' => $skillset->intSkillset_ID,
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 20,
            'bitHasSubProject' => false,
            'stages' => [
                [
                    'step' => 'Tahap Analisis',
                    'start' => '2026-01-01',
                    'end' => '2026-03-31',
                    'plan' => 25,
                    'actual' => 25,
                ],
            ],
        ]);

    $storeResponse->assertRedirect(route('projects.index'));

    $project = MProject::where('txtProjectCode', 'PRJ-TEST-WEB-001')->first();
    $this->assertNotNull($project);
    $this->assertEquals($skillset->intSkillset_ID, $project->intSkillset_ID);
    $this->assertEquals('Web Development', $project->skillset->txtSkillsetName);

    // 3. View detail page and check skillset badge
    $showPage = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->get(route('projects.show', $project));

    $showPage->assertStatus(200);
    $showPage->assertSee('Web Development');

    // 4. Update project to change skillset
    $newSkillset = MSkillset::where('txtSkillsetName', 'AI & Computer Vision')->first();
    $updateResponse = $this->withSession(['auth_user_id' => $head->intUser_ID])
        ->put(route('projects.update', $project), [
            'txtProjectName' => 'Project Portal Web Kalbe Test (Migrated to AI)',
            'txtProjectCode' => 'PRJ-TEST-WEB-001',
            'intProjectType_ID' => $projectType->intProjectType_ID,
            'intSubDepartment_ID' => $subDept->intSubDepartment_ID,
            'intSkillset_ID' => $newSkillset->intSkillset_ID,
            'txtKpiLevel' => 'Individu',
            'floatWeight' => 25,
            'bitHasSubProject' => false,
        ]);

    $updateResponse->assertRedirect(route('projects.show', $project));
    $this->assertEquals($newSkillset->intSkillset_ID, $project->fresh()->intSkillset_ID);
});

test('it renders the guest dashboard on index with 4 stat cards and 4 monthly report charts when unauthenticated', function () {
    $response = $this->get(route('dashboard.index'));

    $response->assertStatus(200);
    $response->assertSee('Total Project & Bobot', false);
    $response->assertSee('Rata-rata Progress Actual');
    $response->assertSee('Project Selesai 100%');
    $response->assertSee('Total Logbook Task (Bulan Ini)');
    $response->assertSee('projectProgressChart');
    $response->assertSee('projectStatusChart');
    $response->assertSee('dailyTrendChart');
    $response->assertSee('subDeptProgressChart');
    $response->assertSee('Masuk / Login');
    $response->assertSee('id="loginModal"', false);
    $response->assertSee('openLoginModal()', false);
    $response->assertSee('name="txtEmail"', false);
    $response->assertSee('name="txtPassword"', false);
    $response->assertSee(route('login.authenticate'));
    // Ensure table and employee cards are NOT rendered for guest
    $response->assertDontSee('Tabel Ringkasan Perkembangan Project');
    $response->assertDontSee('Ringkasan Kinerja & Beban Kerja Per Employee');
    // Ensure navbar (topbar) and sidebar are NOT rendered on guest page
    $response->assertDontSee('<header class="topbar', false);
    $response->assertDontSee('<aside class="sidebar', false);
});

test('it preserves original authenticated dashboard when user is logged in', function () {
    $user = MUser::where('txtRole', 'Head')->first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->get(route('dashboard.index'));

    $response->assertStatus(200);
    $response->assertSee('DASHBOARD KPI & ACTIVITY');
    $response->assertSee('dashboardExposureChart');
    $response->assertSee('Exposure S-Curve Overview');
    // Ensure navbar (topbar) and sidebar ARE rendered for authenticated user
    $response->assertSee('<header class="topbar', false);
    $response->assertSee('<aside class="sidebar', false);
});

test('it redirects to dashboard.index upon logout', function () {
    $user = MUser::first();

    $response = $this->withSession(['auth_user_id' => $user->intUser_ID])
        ->post(route('logout'));

    $response->assertRedirect(route('dashboard.index'));
    expect(session('auth_user_id'))->toBeNull();
});

test('it verifies organizational roles in seeder: NRS as Head, AMI and SNH as Supervisors', function () {
    $nrs = MUser::where('txtEmployeeCode', 'NRS')->first();
    expect($nrs)->not->toBeNull();
    expect($nrs->txtRole)->toBe('Head');
    expect($nrs->txtPosition)->toBe('Department Head MDP');

    $ami = MUser::with('supervisedSubDepartments')->where('txtEmployeeCode', 'AMI')->first();
    expect($ami)->not->toBeNull();
    expect($ami->txtRole)->toBe('Supervisor');
    expect($ami->txtPosition)->toBe('Supervisor MD/IT & AM');
    expect($ami->supervisedSubDepartments->pluck('txtSubDepartmentCode')->all())->toContain('MD/IT', 'AM');

    $snh = MUser::with('supervisedSubDepartments')->where('txtEmployeeCode', 'SNH')->first();
    expect($snh)->not->toBeNull();
    expect($snh->txtRole)->toBe('Supervisor');
    expect($snh->txtPosition)->toBe('Supervisor MO/PPIC & MP/Project');
    expect($snh->supervisedSubDepartments->pluck('txtSubDepartmentCode')->all())->toContain('MO/PPIC', 'MP/Project');
});

test('it restricts Daily Task view so Head and Supervisor only see their own tasks while Superadmin sees all', function () {
    $nrs = MUser::where('txtEmployeeCode', 'NRS')->first();
    $ami = MUser::where('txtEmployeeCode', 'AMI')->first();
    $superadmin = MUser::where('txtRole', 'Superadmin')->first();

    // 1. NRS (Head) only sees NRS tasks
    $responseNrs = $this->withSession(['auth_user_id' => $nrs->intUser_ID])
        ->get(route('reports.daily-tasks'));
    $responseNrs->assertStatus(200);
    $responseNrs->assertViewHas('tasks', function ($tasks) use ($nrs, $ami) {
        return $tasks->every(fn($t) => $t->intUser_ID === $nrs->intUser_ID)
            && ! $tasks->contains('intUser_ID', $ami->intUser_ID);
    });

    // 2. AMI (Supervisor) only sees AMI tasks
    $responseAmi = $this->withSession(['auth_user_id' => $ami->intUser_ID])
        ->get(route('reports.daily-tasks'));
    $responseAmi->assertStatus(200);
    $responseAmi->assertViewHas('tasks', function ($tasks) use ($ami, $nrs) {
        return $tasks->every(fn($t) => $t->intUser_ID === $ami->intUser_ID)
            && ! $tasks->contains('intUser_ID', $nrs->intUser_ID);
    });

    // 3. Superadmin sees all tasks
    $responseAdmin = $this->withSession(['auth_user_id' => $superadmin->intUser_ID])
        ->get(route('reports.daily-tasks'));
    $responseAdmin->assertStatus(200);
    $responseAdmin->assertViewHas('tasks', function ($tasks) use ($nrs, $ami) {
        return $tasks->contains('intUser_ID', $nrs->intUser_ID)
            && $tasks->contains('intUser_ID', $ami->intUser_ID);
    });
});

test('it restricts Daily Plan view and actions so non-superadmin users only see and manage their own cards', function () {
    $nrs = MUser::where('txtEmployeeCode', 'NRS')->first();
    $snh = MUser::where('txtEmployeeCode', 'SNH')->first();
    $superadmin = MUser::where('txtRole', 'Superadmin')->first();

    // 1. NRS (Head) only sees NRS weekly plans
    $responseNrs = $this->withSession(['auth_user_id' => $nrs->intUser_ID])
        ->get(route('reports.daily-plans'));
    $responseNrs->assertStatus(200);
    $responseNrs->assertViewHas('weeklyPlans', function ($plans) use ($nrs, $snh) {
        return $plans->every(fn($p) => $p->intUser_ID === $nrs->intUser_ID)
            && ! $plans->contains('intUser_ID', $snh->intUser_ID);
    });

    // 2. SNH (Supervisor) only sees SNH weekly plans
    $responseSnh = $this->withSession(['auth_user_id' => $snh->intUser_ID])
        ->get(route('reports.daily-plans'));
    $responseSnh->assertStatus(200);
    $responseSnh->assertViewHas('weeklyPlans', function ($plans) use ($snh, $nrs) {
        return $plans->every(fn($p) => $p->intUser_ID === $snh->intUser_ID)
            && ! $plans->contains('intUser_ID', $nrs->intUser_ID);
    });

    // 3. Superadmin sees both NRS and SNH weekly plans
    $responseAdmin = $this->withSession(['auth_user_id' => $superadmin->intUser_ID])
        ->get(route('reports.daily-plans'));
    $responseAdmin->assertStatus(200);
    $responseAdmin->assertViewHas('weeklyPlans', function ($plans) use ($nrs, $snh) {
        return $plans->contains('intUser_ID', $nrs->intUser_ID)
            && $plans->contains('intUser_ID', $snh->intUser_ID);
    });

    // 4. SNH cannot view or mutate NRS weekly plan
    $nrsPlan = MWeeklyPlan::where('intUser_ID', $nrs->intUser_ID)->first();
    $forbiddenShow = $this->withSession(['auth_user_id' => $snh->intUser_ID])
        ->get(route('daily-plans.show', $nrsPlan));
    $forbiddenShow->assertStatus(403);

    $forbiddenDelete = $this->withSession(['auth_user_id' => $snh->intUser_ID])
        ->delete(route('daily-plans.destroy', $nrsPlan));
    $forbiddenDelete->assertStatus(403);
});
