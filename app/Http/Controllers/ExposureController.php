<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Support\ExposureCurveBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ExposureController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $projectQuery = MProject::with([
            'subDepartment',
            'projectType',
            'user',
            'subProjects.stages',
            'directStages',
        ])
            ->where('bitActive', true);

        if (! $authUser->isSuperadmin()) {
            $projectQuery->where('intDepartment_ID', $departmentId);
        }

        $projects = $projectQuery->orderBy('txtProjectName')->get();
        $employees = MUser::where('bitActive', true)
            ->where('intDepartment_ID', $departmentId)
            ->where('txtRole', 'Employee')
            ->orderBy('txtEmployeeName')
            ->get();

        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)
            ->where('bitActive', true)
            ->get();

        $projectTypes = MProjectType::where('bitActive', true)->get();
        $exposurePayload = ExposureCurveBuilder::payload($projects);
        $exposurePayload['employees'] = $employees->map(fn ($emp) => [
            'id' => (string) $emp->intUser_ID,
            'name' => $emp->txtEmployeeName,
            'subDept' => $emp->subDepartment?->txtSubDepartmentName ?: '-',
        ])->values()->all();

        $summary = [
            'totalProjects' => $projects->count(),
            'totalStages' => $projects->sum(fn ($p) => $p->bitHasSubProject
                ? $p->subProjects->sum(fn ($sub) => $sub->stages->count())
                : $p->directStages->count()),
            'avgActual' => $projects->count() > 0 ? round($projects->avg('floatActual'), 1) : 0,
            'avgPlan' => $projects->count() > 0 ? round($projects->avg('floatPlan'), 1) : 0,
        ];

        return view('exposure.index', compact(
            'projects',
            'employees',
            'subDepartments',
            'projectTypes',
            'exposurePayload',
            'summary',
            'authUser'
        ));
    }
}
