<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyReportController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = $request->get('month', date('Y-m'));
        $selectedSubDept = $request->get('subdept');
        $selectedEmployee = $request->get('employee');
        $selectedType = $request->get('type');

        $query = MProject::with(['subDepartment', 'projectType', 'user', 'subProjects', 'directStages'])
            ->active()
            ->orderBy('txtKpiLevel')
            ->orderBy('intProject_ID');

        if ($selectedSubDept) {
            $query->where('intSubDepartment_ID', $selectedSubDept);
        }

        if ($selectedEmployee) {
            $query->where('intUser_ID', $selectedEmployee);
        }

        if ($selectedType) {
            $query->where('intProjectType_ID', $selectedType);
        }

        $projects = $query->get();

        $subDepartments = MSubDepartment::active()->orderBy('txtSubDepartmentCode')->get();
        $allEmployees = MUser::active()->where('txtRole', 'Employee')->orderBy('txtEmployeeName')->get();
        $projectTypes = MProjectType::active()->orderBy('txtProjectTypeCode')->get();

        // Summary calculations
        $totalWeight = (float) $projects->sum('floatWeight');
        $totalScore = (int) $projects->sum('intScore');
        $scoredProjects = $projects->filter(fn ($p) => $p->intScore > 0);
        $avgScore = $scoredProjects->count() > 0 ? round($scoredProjects->avg('intScore'), 1) : 0;
        $avgActual = $projects->count() > 0 ? round($projects->avg('floatActual'), 1) : 0;

        // Employee performance matrix summary
        $employeeCards = $allEmployees->map(function ($emp) {
            $empProjects = MProject::where('intUser_ID', $emp->intUser_ID)->active()->get();
            $scored = $empProjects->filter(fn ($p) => $p->intScore > 0);

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

        return view('reports.monthly-report', [
            'projects' => $projects,
            'subDepartments' => $subDepartments,
            'allEmployees' => $allEmployees,
            'projectTypes' => $projectTypes,
            'selectedMonth' => $selectedMonth,
            'selectedSubDept' => $selectedSubDept,
            'selectedEmployee' => $selectedEmployee,
            'selectedType' => $selectedType,
            'totalWeight' => $totalWeight,
            'totalScore' => $totalScore,
            'avgScore' => $avgScore,
            'avgActual' => $avgActual,
            'employees' => $employeeCards,
        ]);
    }
}
