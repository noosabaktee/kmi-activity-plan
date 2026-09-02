<?php

namespace App\Support;

use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MUser;
use Illuminate\Support\Collection;

class ExposureCurveBuilder
{
    /**
     * Build exposure payload for API and controllers.
     */
    public static function build(?Collection $projects = null): array
    {
        if ($projects === null) {
            $projects = MProject::with([
                'department',
                'subDepartment',
                'projectType',
                'user',
                'subProjects.stages',
                'directStages',
            ])->active()->get();
        }

        $projectTypes = MProjectType::where('bitActive', true)->get();
        $totalWeight = $projectTypes->sum('floatDefaultWeight');

        $typesData = $projectTypes->map(function (MProjectType $type) use ($totalWeight) {
            return [
                'id' => (string) $type->intProjectType_ID,
                'key' => strtolower($type->txtProjectTypeCode),
                'code' => $type->txtProjectTypeCode,
                'label' => $type->txtProjectTypeName,
                'color' => $type->txtColor ?: '#006838',
                'icon' => $type->txtIcon ?: 'fa-solid fa-folder-tree',
                'weight' => (float) $type->floatDefaultWeight,
                'share' => $totalWeight > 0 ? round(($type->floatDefaultWeight / $totalWeight) * 100, 2) : 20.0,
            ];
        })->values()->all();

        $employees = MUser::with(['subDepartment'])->where('bitActive', true)->where('txtRole', 'Employee')->get();
        $employeesData = $employees->map(function (MUser $emp) {
            return [
                'id' => (string) $emp->intUser_ID,
                'name' => $emp->txtEmployeeName,
                'code' => $emp->txtEmployeeCode,
                'subDept' => $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP',
                'role' => $emp->txtRole,
            ];
        })->values()->all();

        return [
            'generatedAt' => now()->format('d M Y H:i'),
            'projectTypes' => $typesData,
            'employees' => $employeesData,
            'projects' => $projects->map(fn (MProject $project) => self::projectPayload($project, $typesData))->values()->all(),
        ];
    }

    /**
     * Build exposure payload for projects collection.
     */
    public static function payload(Collection $projects): array
    {
        return self::build($projects);
    }

    /**
     * Build individual project payload.
     */
    public static function projectPayload(MProject $project, array $typesData): array
    {
        $typeCode = $project->projectType?->txtProjectTypeCode ?: 'IPP';
        $typeKey = strtolower($typeCode);
        $type = collect($typesData)->firstWhere('key', $typeKey);

        $stages = collect();

        if ($project->bitHasSubProject) {
            $subProjects = $project->subProjects;
            $totalSubWeight = $subProjects->sum('floatWeight');

            foreach ($subProjects as $sub) {
                $subWeightFactor = $totalSubWeight > 0 ? ($sub->floatWeight / $totalSubWeight) : (1 / max(1, $subProjects->count()));
                foreach ($sub->stages as $st) {
                    $stages->push([
                        'number' => (int) $st->intProjectStageNumber,
                        'step' => "{$sub->txtSubProjectName} - {$st->txtProjectStageStep}",
                        'start' => $st->dtmProjectStageStartDate?->format('Y-m-d'),
                        'end' => $st->dtmProjectStageEndDate?->format('Y-m-d'),
                        'plan' => round($st->floatProjectStagePlan * $subWeightFactor, 2),
                        'actual' => round($st->floatProjectStageActual * $subWeightFactor, 2),
                        'weight' => round($st->floatProjectStageWeight * $subWeightFactor, 2),
                    ]);
                }
            }
        } else {
            foreach ($project->directStages as $st) {
                $stages->push([
                    'number' => (int) $st->intProjectStageNumber,
                    'step' => $st->txtProjectStageStep,
                    'start' => $st->dtmProjectStageStartDate?->format('Y-m-d'),
                    'end' => $st->dtmProjectStageEndDate?->format('Y-m-d'),
                    'plan' => (float) $st->floatProjectStagePlan,
                    'actual' => (float) $st->floatProjectStageActual,
                    'weight' => (float) $st->floatProjectStageWeight,
                ]);
            }
        }

        return [
            'id' => (string) $project->intProject_ID,
            'name' => $project->txtProjectName,
            'code' => $project->txtProjectCode,
            'type' => $typeCode,
            'typeKey' => $typeKey,
            'kpiLevel' => $project->txtKpiLevel,
            'deliverable' => $project->txtDeliverable,
            'score' => $project->intScore,
            'achievement' => $project->txtAchievement,
            'weight' => (float) $project->floatWeight,
            'start' => $project->dtmProjectStartDate?->format('Y-m-d'),
            'end' => $project->dtmProjectEndDate?->format('Y-m-d'),
            'actual' => (float) $project->floatActual,
            'planned' => (float) $project->floatPlan,
            'hasSubProject' => (bool) $project->bitHasSubProject,
            'employeeId' => (string) $project->intUser_ID,
            'employeeName' => $project->user?->txtEmployeeName ?? 'Unassigned',
            'subDeptCode' => $project->subDepartment?->txtSubDepartmentCode ?? 'MDP',
            'stages' => $stages->sortBy('number')->values()->all(),
            'subProjects' => $project->bitHasSubProject ? $project->subProjects->map(fn ($sp) => [
                'id' => $sp->intSubProject_ID,
                'name' => $sp->txtSubProjectName,
                'weight' => (float) $sp->floatWeight,
                'progress' => (float) $sp->floatProgress,
                'score' => $sp->intScore,
                'stageCount' => $sp->stages->count(),
            ])->values()->all() : [],
        ];
    }
}
