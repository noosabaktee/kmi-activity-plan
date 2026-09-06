<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyTask;
use App\Support\ExposureCurveBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = session('auth_user_id');
        $authUser = $userId ? MUser::with(['department', 'subDepartment'])->where('bitActive', true)->find($userId) : null;

        if (! $authUser) {
            $monthlyData = MonthlyReportController::getMonthlyReportData();

            return view('dashboard.guest', $monthlyData);
        }

        $departmentId = $authUser->intDepartment_ID ?: 1;

        // Base query scoped by department (unless superadmin filter is applied)
        $projectQuery = MProject::with(['subDepartment', 'projectType', 'user', 'subProjects.stages', 'directStages'])
            ->where('bitActive', true);

        if (! $authUser->isSuperadmin()) {
            $projectQuery->where('intDepartment_ID', $departmentId);
        }

        $projects = $projectQuery->get();
        $employees = MUser::where('bitActive', true)
            ->where('intDepartment_ID', $departmentId)
            ->where('txtRole', '!=', 'Superadmin')
            ->get();

        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)
            ->where('bitActive', true)
            ->withCount(['projects' => function ($q) {
                $q->where('bitActive', true);
            }])
            ->get();

        $projectTypes = MProjectType::where('bitActive', true)
            ->withCount(['projects' => function ($q) use ($departmentId, $authUser) {
                $q->where('bitActive', true);
                if (! $authUser->isSuperadmin()) {
                    $q->where('intDepartment_ID', $departmentId);
                }
            }])
            ->get();

        $recentTasks = TrDailyTask::with(['user', 'project.projectType', 'projectType', 'subProject', 'stage'])
            ->where('intDepartment_ID', $departmentId)
            ->orderByDesc('dtmTaskDate')
            ->take(8)
            ->get();

        $recentWeeklyPlans = MWeeklyPlan::with(['user', 'activities'])
            ->where('intDepartment_ID', $departmentId)
            ->where('bitActive', true)
            ->orderByDesc('dtmWeekStartDate')
            ->take(5)
            ->get();

        // Calculate KPI Metrics
        $totalProjects = $projects->count();
        $avgActual = $totalProjects > 0 ? round($projects->avg('floatActual'), 1) : 0;
        $avgScore = $totalProjects > 0 ? round($projects->avg('intScore'), 1) : 0;
        $totalWeight = $projects->sum('floatWeight');

        $exposurePayload = ExposureCurveBuilder::payload($projects);
        $monthlyReportData = MonthlyReportController::getMonthlyReportData();
        $ytdData = $monthlyReportData['ytdData'] ?? null;

        return view('dashboard.index', compact(
            'authUser',
            'projects',
            'employees',
            'subDepartments',
            'projectTypes',
            'recentTasks',
            'recentWeeklyPlans',
            'totalProjects',
            'avgActual',
            'avgScore',
            'totalWeight',
            'exposurePayload',
            'ytdData'
        ));
    }
}
