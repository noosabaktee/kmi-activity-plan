<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrDailyTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MonthlyReportController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = $request->get('month', date('Y-m'));
        $selectedSubDept = $request->get('subdept');
        $selectedEmployee = $request->get('employee');
        $selectedType = $request->get('type');

        $data = self::getMonthlyReportData($selectedMonth, $selectedSubDept, $selectedEmployee, $selectedType);

        return view('reports.monthly-report', $data);
    }

    public static function getMonthlyReportData(?string $selectedMonth = null, $selectedSubDept = null, $selectedEmployee = null, $selectedType = null): array
    {
        $selectedMonth = $selectedMonth ?: date('Y-m');

        $query = MProject::with(['subDepartment', 'projectType', 'user', 'subProjects', 'directStages'])
            ->active()
            ->orderBy('txtKpiLevel')
            ->orderBy('intProject_ID');

        if ($selectedSubDept) {
            $query->where('intSubDepartment_ID', $selectedSubDept);
        }

        if ($selectedEmployee) {
            $query->forUser($selectedEmployee);
        }

        if ($selectedType) {
            $query->where('intProjectType_ID', $selectedType);
        }

        $projects = $query->get();

        $subDepartments = MSubDepartment::active()->orderBy('txtSubDepartmentCode')->get();
        $allEmployees = MUser::active()->where('txtRole', 'Employee')->orderBy('txtEmployeeName')->get();
        $projectTypes = MProjectType::active()->orderBy('txtProjectTypeCode')->get();

        // 1. Summary KPIs
        $totalProjects = $projects->count();
        $totalWeight = (float) $projects->sum('floatWeight');
        $totalScore = (int) $projects->sum('intScore');
        $scoredProjects = $projects->filter(fn($p) => $p->intScore > 0);
        $avgScore = $scoredProjects->count() > 0 ? round($scoredProjects->avg('intScore'), 1) : 0;
        $avgActual = $totalProjects > 0 ? round($projects->avg('floatActual'), 1) : 0;
        $completedProjectsCount = $projects->filter(fn($p) => $p->floatActual >= 100 || strtolower($p->txtStatus) === 'completed')->count();
        $completionRate = $totalProjects > 0 ? round(($completedProjectsCount / $totalProjects) * 100, 1) : 0;

        // 2. Chart Dataset 1: Project Plan vs Actual Progress (Bar Chart)
        $chartProjectLabels = [];
        $chartProjectPlans = [];
        $chartProjectActuals = [];
        foreach ($projects as $p) {
            $chartProjectLabels[] = Str::limit($p->txtProjectName, 22);
            $chartProjectPlans[] = (float) ($p->floatPlan ?: 100);
            $chartProjectActuals[] = (float) ($p->floatActual ?: 0);
        }

        // 3. Chart Dataset 2: Status & Health Breakdown (Doughnut Chart)
        $statusCounts = [
            'completed' => 0,     // 100%
            'on_track' => 0,      // 75% - 99%
            'in_progress' => 0,   // 40% - 74%
            'needs_action' => 0,  // < 40%
        ];
        foreach ($projects as $p) {
            $actual = (float) $p->floatActual;
            if ($actual >= 100 || strtolower($p->txtStatus) === 'completed') {
                $statusCounts['completed']++;
            } elseif ($actual >= 75) {
                $statusCounts['on_track']++;
            } elseif ($actual >= 40) {
                $statusCounts['in_progress']++;
            } else {
                $statusCounts['needs_action']++;
            }
        }

        // 4. Chart Dataset 3: Daily Task Activity & Momentum Trend in Selected Month (Line Chart)
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Exception $e) {
            $monthCarbon = Carbon::now();
        }
        $startOfMonth = $monthCarbon->copy()->startOfMonth();
        $endOfMonth = $monthCarbon->copy()->endOfMonth();

        $dailyTaskQuery = TrDailyTask::whereBetween('dtmTaskDate', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
        if ($selectedSubDept) {
            $dailyTaskQuery->where('intSubDepartment_ID', $selectedSubDept);
        }
        if ($selectedEmployee) {
            $dailyTaskQuery->where('intUser_ID', $selectedEmployee);
        }
        $dailyTasks = $dailyTaskQuery->get();

        // Group tasks by date
        $tasksByDate = $dailyTasks->groupBy(fn($t) => Carbon::parse($t->dtmTaskDate)->format('d M'));
        $chartTrendLabels = [];
        $chartTrendCounts = [];
        $chartTrendHours = [];

        // Fill days of month (e.g. up to 31 days or weekly intervals if month is large)
        $daysInMonth = $endOfMonth->day;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = $monthCarbon->copy()->day($d);
            $key = $dateObj->format('d M');
            $chartTrendLabels[] = $d; // Day number 1..31
            $group = $tasksByDate->get($key, collect());
            $chartTrendCounts[] = $group->count();
            $chartTrendHours[] = round($group->sum('floatDurationHours'), 1);
        }

        // 5. Chart Dataset 4: Sub-Department Performance & Weight Comparison (Grouped Bar)
        $chartSubDeptLabels = [];
        $chartSubDeptAvgProgress = [];
        $chartSubDeptTotalWeight = [];
        $chartSubDeptProjectCount = [];
        foreach ($subDepartments as $sd) {
            $sdProjects = MProject::where('intSubDepartment_ID', $sd->intSubDepartment_ID)->active()->get();
            if ($selectedEmployee) {
                $sdProjects = $sdProjects->where('intUser_ID', $selectedEmployee);
            }
            if ($selectedType) {
                $sdProjects = $sdProjects->where('intProjectType_ID', $selectedType);
            }

            $chartSubDeptLabels[] = $sd->txtSubDepartmentCode;
            $chartSubDeptAvgProgress[] = $sdProjects->count() > 0 ? round($sdProjects->avg('floatActual'), 1) : 0;
            $chartSubDeptTotalWeight[] = (float) $sdProjects->sum('floatWeight');
            $chartSubDeptProjectCount[] = $sdProjects->count();
        }

        // 6. Employee Performance Summary Cards
        $employeeCards = $allEmployees->map(function ($emp) {
            $empProjects = MProject::where('intUser_ID', $emp->intUser_ID)->active()->get();
            $scored = $empProjects->filter(fn($p) => $p->intScore > 0);

            return [
                'id' => $emp->intUser_ID,
                'name' => $emp->txtEmployeeName,
                'subDept' => $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP',
                'position' => $emp->txtRole,
                'totalProjects' => $empProjects->count(),
                'totalWeight' => (float) $empProjects->sum('floatWeight'),
                'avgScore' => $scored->count() > 0 ? round($scored->avg('intScore'), 1) : 0,
                'avgActual' => $empProjects->count() > 0 ? round($empProjects->avg('floatActual'), 1) : 0,
            ];
        });

        return [
            'projects' => $projects,
            'subDepartments' => $subDepartments,
            'allEmployees' => $allEmployees,
            'projectTypes' => $projectTypes,
            'selectedMonth' => $selectedMonth,
            'selectedSubDept' => $selectedSubDept,
            'selectedEmployee' => $selectedEmployee,
            'selectedType' => $selectedType,
            'totalProjects' => $totalProjects,
            'totalWeight' => $totalWeight,
            'totalScore' => $totalScore,
            'avgScore' => $avgScore,
            'avgActual' => $avgActual,
            'completedProjectsCount' => $completedProjectsCount,
            'completionRate' => $completionRate,
            'employees' => $employeeCards,
            // Chart Payloads
            'chartPayload' => [
                'projectLabels' => $chartProjectLabels,
                'projectPlans' => $chartProjectPlans,
                'projectActuals' => $chartProjectActuals,
                'statusCounts' => $statusCounts,
                'trendLabels' => $chartTrendLabels,
                'trendCounts' => $chartTrendCounts,
                'trendHours' => $chartTrendHours,
                'subDeptLabels' => $chartSubDeptLabels,
                'subDeptAvgProgress' => $chartSubDeptAvgProgress,
                'subDeptTotalWeight' => $chartSubDeptTotalWeight,
                'subDeptProjectCount' => $chartSubDeptProjectCount,
            ],
            'chartProjectLabels' => $chartProjectLabels,
            'chartProjectPlans' => $chartProjectPlans,
            'chartProjectActuals' => $chartProjectActuals,
            'statusCounts' => $statusCounts,
            'chartTrendLabels' => $chartTrendLabels,
            'chartTrendCounts' => $chartTrendCounts,
            'chartTrendHours' => $chartTrendHours,
            'chartSubDeptLabels' => $chartSubDeptLabels,
            'chartSubDeptAvgProgress' => $chartSubDeptAvgProgress,
            'chartSubDeptTotalWeight' => $chartSubDeptTotalWeight,
            'chartSubDeptProjectCount' => $chartSubDeptProjectCount,
            'monthName' => $monthCarbon->translatedFormat('F Y'),
        ];
    }
}
