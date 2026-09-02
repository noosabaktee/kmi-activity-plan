<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrProjectStage;
use App\Models\TrSubProject;
use App\Support\ExposureCurveBuilder;
use App\Support\RoleAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $query = MProject::with(['subDepartment', 'projectType', 'user', 'subProjects.stages', 'directStages'])
            ->where('bitActive', true);

        if (! $authUser->isSuperadmin()) {
            $query->where('intDepartment_ID', $departmentId);
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('intProjectType_ID', $request->input('type'));
        }

        if ($request->filled('subdept')) {
            $query->where('intSubDepartment_ID', $request->input('subdept'));
        }

        if ($request->filled('employee')) {
            $query->where('intUser_ID', $request->input('employee'));
        }

        if ($request->filled('kpi_level')) {
            $query->where('txtKpiLevel', $request->input('kpi_level'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('txtProjectName', 'like', "%{$search}%")
                    ->orWhere('txtProjectCode', 'like', "%{$search}%")
                    ->orWhere('txtDeliverable', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('txtProjectName')->get();
        $projectTypes = MProjectType::where('bitActive', true)->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();

        $summary = [
            'totalProjects' => $projects->count(),
            'singleProjects' => $projects->where('bitHasSubProject', false)->count(),
            'multiProjects' => $projects->where('bitHasSubProject', true)->count(),
            'avgProgress' => $projects->count() > 0 ? round($projects->avg('floatActual'), 1) : 0,
            'totalWeight' => $projects->sum('floatWeight'),
        ];

        return view('projects.index', compact('projects', 'projectTypes', 'subDepartments', 'employees', 'summary', 'authUser'));
    }

    public function create(): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $projectTypes = MProjectType::where('bitActive', true)->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();

        return view('projects.create', compact('projectTypes', 'subDepartments', 'employees', 'authUser'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));

        $validated = $request->validate([
            'txtProjectName' => ['required', 'string', 'max:255'],
            'txtProjectCode' => ['nullable', 'string', 'max:50'],
            'intProjectType_ID' => ['required', 'exists:mProjectType,intProjectType_ID'],
            'intSubDepartment_ID' => ['nullable', 'exists:mSubDepartment,intSubDepartment_ID'],
            'intUser_ID' => ['required', 'exists:mUser,intUser_ID'],
            'txtKpiLevel' => ['required', 'string'],
            'txtDeliverable' => ['nullable', 'string'],
            'txtTargetSkalaGrade' => ['nullable', 'string'],
            'intScore' => ['nullable', 'integer', 'min:1', 'max:5'],
            'txtAchievement' => ['nullable', 'string', 'max:255'],
            'floatWeight' => ['required', 'numeric', 'min:0'],
            'bitHasSubProject' => ['nullable', 'boolean'],
            'txtDescription' => ['nullable', 'string'],
            'dtmProjectStartDate' => ['nullable', 'date'],
            'dtmProjectEndDate' => ['nullable', 'date', 'after_or_equal:dtmProjectStartDate'],
            'txtStatus' => ['nullable', 'string'],
            // Direct Stages (for Single Project)
            'stages' => ['nullable', 'array'],
            'stages.*.step' => ['nullable', 'string', 'max:255'],
            'stages.*.start' => ['nullable', 'date'],
            'stages.*.end' => ['nullable', 'date'],
            'stages.*.plan' => ['nullable', 'numeric', 'min:0'],
            'stages.*.actual' => ['nullable', 'numeric', 'min:0'],
            // Sub Projects (for Multi Sub-Project)
            'sub_projects' => ['nullable', 'array'],
            'sub_projects.*.name' => ['nullable', 'string', 'max:255'],
            'sub_projects.*.deliverable' => ['nullable', 'string'],
            'sub_projects.*.target_grade' => ['nullable', 'string'],
            'sub_projects.*.score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sub_projects.*.achievement' => ['nullable', 'string'],
            'sub_projects.*.weight' => ['nullable', 'numeric', 'min:0'],
            'sub_projects.*.stages' => ['nullable', 'array'],
        ]);

        $hasSubProject = $request->boolean('bitHasSubProject');
        $now = now();

        DB::transaction(function () use ($validated, $hasSubProject, $authUser, $now) {
            $project = MProject::create([
                'intDepartment_ID' => $authUser->intDepartment_ID ?: 1,
                'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? $authUser->intSubDepartment_ID,
                'intProjectType_ID' => $validated['intProjectType_ID'],
                'intUser_ID' => $validated['intUser_ID'],
                'txtProjectCode' => !empty($validated['txtProjectCode']) ? $validated['txtProjectCode'] : ('PRJ-' . date('Y') . '-' . str_pad((MProject::max('intProject_ID') ?? 0) + 1, 3, '0', STR_PAD_LEFT)),
                'txtProjectName' => $validated['txtProjectName'],
                'txtKpiLevel' => $validated['txtKpiLevel'],
                'txtDeliverable' => $validated['txtDeliverable'] ?? null,
                'txtTargetSkalaGrade' => $validated['txtTargetSkalaGrade'] ?? null,
                'intScore' => $validated['intScore'] ?? null,
                'txtAchievement' => $validated['txtAchievement'] ?? null,
                'floatWeight' => (float) $validated['floatWeight'],
                'bitHasSubProject' => $hasSubProject,
                'txtDescription' => $validated['txtDescription'] ?? null,
                'dtmProjectStartDate' => $validated['dtmProjectStartDate'] ?? null,
                'dtmProjectEndDate' => $validated['dtmProjectEndDate'] ?? null,
                'floatPlan' => 100,
                'floatActual' => 0,
                'txtStatus' => $validated['txtStatus'] ?? 'In Progress',
                'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            if ($hasSubProject && ! empty($validated['sub_projects'])) {
                foreach ($validated['sub_projects'] as $subData) {
                    if (empty($subData['name'])) {
                        continue;
                    }

                    $subProject = TrSubProject::create([
                        'intProject_ID' => $project->intProject_ID,
                        'txtSubProjectName' => $subData['name'],
                        'txtDeliverable' => $subData['deliverable'] ?? null,
                        'txtTargetSkalaGrade' => $subData['target_grade'] ?? null,
                        'intScore' => ! empty($subData['score']) ? (int) $subData['score'] : null,
                        'txtAchievement' => $subData['achievement'] ?? null,
                        'floatWeight' => (float) ($subData['weight'] ?? 0),
                        'floatProgress' => 0,
                        'dtmStartDate' => $project->dtmProjectStartDate,
                        'dtmEndDate' => $project->dtmProjectEndDate,
                        'txtStatus' => 'In Progress',
                        'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                        'dtmInserted' => $now,
                    ]);

                    if (! empty($subData['stages'])) {
                        foreach ($subData['stages'] as $sIdx => $stData) {
                            if (empty($stData['step'])) {
                                continue;
                            }
                            TrProjectStage::create([
                                'intProject_ID' => $project->intProject_ID,
                                'intSubProject_ID' => $subProject->intSubProject_ID,
                                'intProjectStageNumber' => $sIdx + 1,
                                'txtProjectStageStep' => $stData['step'],
                                'dtmProjectStageStartDate' => $stData['start'] ?? $project->dtmProjectStartDate,
                                'dtmProjectStageEndDate' => $stData['end'] ?? $project->dtmProjectEndDate,
                                'floatProjectStagePlan' => (float) ($stData['plan'] ?? 0),
                                'floatProjectStageActual' => (float) ($stData['actual'] ?? 0),
                                'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                                'dtmInserted' => $now,
                            ]);
                        }
                        $subProject->recalculateProgress();
                    }
                }
            } elseif (! empty($validated['stages'])) {
                // Direct stages
                foreach ($validated['stages'] as $sIdx => $stData) {
                    if (empty($stData['step'])) {
                        continue;
                    }
                    TrProjectStage::create([
                        'intProject_ID' => $project->intProject_ID,
                        'intSubProject_ID' => null,
                        'intProjectStageNumber' => $sIdx + 1,
                        'txtProjectStageStep' => $stData['step'],
                        'dtmProjectStageStartDate' => $stData['start'] ?? $project->dtmProjectStartDate,
                        'dtmProjectStageEndDate' => $stData['end'] ?? $project->dtmProjectEndDate,
                        'floatProjectStagePlan' => (float) ($stData['plan'] ?? 0),
                        'floatProjectStageActual' => (float) ($stData['actual'] ?? 0),
                        'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                        'dtmInserted' => $now,
                    ]);
                }
            }

            $project->recalculateProgress();
        });

        return redirect()->route('projects.index')->with('success', 'Project berhasil dibuat!');
    }

    public function show(MProject $project): View
    {
        $project->load(['department', 'subDepartment', 'projectType', 'user', 'subProjects.stages', 'directStages', 'dailyTasks.user']);
        $typesData = ExposureCurveBuilder::payload(collect([$project]))['projectTypes'];
        $projectPayload = ExposureCurveBuilder::projectPayload($project, $typesData);

        return view('projects.show', compact('project', 'projectPayload'));
    }

    public function edit(MProject $project): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $project->load(['subProjects.stages', 'directStages']);
        $projectTypes = MProjectType::where('bitActive', true)->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();

        return view('projects.edit', compact('project', 'projectTypes', 'subDepartments', 'employees', 'authUser'));
    }

    public function update(Request $request, MProject $project): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));

        $validated = $request->validate([
            'txtProjectName' => ['required', 'string', 'max:255'],
            'txtProjectCode' => ['nullable', 'string', 'max:50'],
            'intProjectType_ID' => ['required', 'exists:mProjectType,intProjectType_ID'],
            'intSubDepartment_ID' => ['nullable', 'exists:mSubDepartment,intSubDepartment_ID'],
            'intUser_ID' => ['required', 'exists:mUser,intUser_ID'],
            'txtKpiLevel' => ['required', 'string'],
            'txtDeliverable' => ['nullable', 'string'],
            'txtTargetSkalaGrade' => ['nullable', 'string'],
            'intScore' => ['nullable', 'integer', 'min:1', 'max:5'],
            'txtAchievement' => ['nullable', 'string', 'max:255'],
            'floatWeight' => ['required', 'numeric', 'min:0'],
            'bitHasSubProject' => ['nullable', 'boolean'],
            'txtDescription' => ['nullable', 'string'],
            'dtmProjectStartDate' => ['nullable', 'date'],
            'dtmProjectEndDate' => ['nullable', 'date', 'after_or_equal:dtmProjectStartDate'],
            'txtStatus' => ['nullable', 'string'],
            'stages' => ['nullable', 'array'],
            'sub_projects' => ['nullable', 'array'],
        ]);

        $hasSubProject = $request->boolean('bitHasSubProject');
        $now = now();

        DB::transaction(function () use ($project, $validated, $hasSubProject, $authUser, $now) {
            $project->update([
                'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? $project->intSubDepartment_ID,
                'intProjectType_ID' => $validated['intProjectType_ID'],
                'intUser_ID' => $validated['intUser_ID'],
                'txtProjectCode' => $validated['txtProjectCode'] ?? $project->txtProjectCode,
                'txtProjectName' => $validated['txtProjectName'],
                'txtKpiLevel' => $validated['txtKpiLevel'],
                'txtDeliverable' => $validated['txtDeliverable'] ?? null,
                'txtTargetSkalaGrade' => $validated['txtTargetSkalaGrade'] ?? null,
                'intScore' => $validated['intScore'] ?? null,
                'txtAchievement' => $validated['txtAchievement'] ?? null,
                'floatWeight' => (float) $validated['floatWeight'],
                'bitHasSubProject' => $hasSubProject,
                'txtDescription' => $validated['txtDescription'] ?? null,
                'dtmProjectStartDate' => $validated['dtmProjectStartDate'] ?? null,
                'dtmProjectEndDate' => $validated['dtmProjectEndDate'] ?? null,
                'txtStatus' => $validated['txtStatus'] ?? $project->txtStatus,
                'txtUpdatedBy' => $authUser->txtEmail ?? 'system',
                'dtmUpdated' => $now,
            ]);

            // If sub-projects provided
            if ($hasSubProject) {
                // Delete previous direct stages
                TrProjectStage::where('intProject_ID', $project->intProject_ID)->whereNull('intSubProject_ID')->delete();

                // Sync sub projects
                if (! empty($validated['sub_projects'])) {
                    $keepSubIds = [];
                    foreach ($validated['sub_projects'] as $subData) {
                        if (empty($subData['name'])) {
                            continue;
                        }

                        $subId = ! empty($subData['id']) ? (int) $subData['id'] : null;
                        if ($subId && $existingSub = TrSubProject::where('intProject_ID', $project->intProject_ID)->find($subId)) {
                            $existingSub->update([
                                'txtSubProjectName' => $subData['name'],
                                'txtDeliverable' => $subData['deliverable'] ?? null,
                                'txtTargetSkalaGrade' => $subData['target_grade'] ?? null,
                                'intScore' => ! empty($subData['score']) ? (int) $subData['score'] : null,
                                'txtAchievement' => $subData['achievement'] ?? null,
                                'floatWeight' => (float) ($subData['weight'] ?? 0),
                            ]);
                            $subProject = $existingSub;
                        } else {
                            $subProject = TrSubProject::create([
                                'intProject_ID' => $project->intProject_ID,
                                'txtSubProjectName' => $subData['name'],
                                'txtDeliverable' => $subData['deliverable'] ?? null,
                                'txtTargetSkalaGrade' => $subData['target_grade'] ?? null,
                                'intScore' => ! empty($subData['score']) ? (int) $subData['score'] : null,
                                'txtAchievement' => $subData['achievement'] ?? null,
                                'floatWeight' => (float) ($subData['weight'] ?? 0),
                                'floatProgress' => 0,
                                'dtmStartDate' => $project->dtmProjectStartDate,
                                'dtmEndDate' => $project->dtmProjectEndDate,
                                'txtStatus' => 'In Progress',
                                'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                                'dtmInserted' => $now,
                            ]);
                        }
                        $keepSubIds[] = $subProject->intSubProject_ID;

                        // Stages for this sub-project
                        if (isset($subData['stages']) && is_array($subData['stages'])) {
                            TrProjectStage::where('intSubProject_ID', $subProject->intSubProject_ID)->delete();
                            foreach ($subData['stages'] as $sIdx => $stData) {
                                if (empty($stData['step'])) {
                                    continue;
                                }
                                TrProjectStage::create([
                                    'intProject_ID' => $project->intProject_ID,
                                    'intSubProject_ID' => $subProject->intSubProject_ID,
                                    'intProjectStageNumber' => $sIdx + 1,
                                    'txtProjectStageStep' => $stData['step'],
                                    'dtmProjectStageStartDate' => $stData['start'] ?? $project->dtmProjectStartDate,
                                    'dtmProjectStageEndDate' => $stData['end'] ?? $project->dtmProjectEndDate,
                                    'floatProjectStagePlan' => (float) ($stData['plan'] ?? 0),
                                    'floatProjectStageActual' => (float) ($stData['actual'] ?? 0),
                                    'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                                    'dtmInserted' => $now,
                                ]);
                            }
                            $subProject->recalculateProgress();
                        }
                    }

                    // Delete removed sub-projects
                    TrSubProject::where('intProject_ID', $project->intProject_ID)->whereNotIn('intSubProject_ID', $keepSubIds)->delete();
                }
            } else {
                // Single Project: remove sub projects & sync direct stages
                TrSubProject::where('intProject_ID', $project->intProject_ID)->delete();
                TrProjectStage::where('intProject_ID', $project->intProject_ID)->delete();

                if (! empty($validated['stages'])) {
                    foreach ($validated['stages'] as $sIdx => $stData) {
                        if (empty($stData['step'])) {
                            continue;
                        }
                        TrProjectStage::create([
                            'intProject_ID' => $project->intProject_ID,
                            'intSubProject_ID' => null,
                            'intProjectStageNumber' => $sIdx + 1,
                            'txtProjectStageStep' => $stData['step'],
                            'dtmProjectStageStartDate' => $stData['start'] ?? $project->dtmProjectStartDate,
                            'dtmProjectStageEndDate' => $stData['end'] ?? $project->dtmProjectEndDate,
                            'floatProjectStagePlan' => (float) ($stData['plan'] ?? 0),
                            'floatProjectStageActual' => (float) ($stData['actual'] ?? 0),
                            'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                            'dtmInserted' => $now,
                        ]);
                    }
                }
            }

            $project->recalculateProgress();
        });

        return redirect()->route('projects.show', $project)->with('success', 'Project berhasil diperbarui!');
    }

    public function destroy(MProject $project): RedirectResponse
    {
        $project->update(['bitActive' => false]);

        return redirect()->route('projects.index')->with('success', 'Project berhasil dihapus.');
    }
}
