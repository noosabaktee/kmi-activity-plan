<?php

namespace App\Http\Controllers;

use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MUser;
use App\Models\TrDailyTask;
use App\Models\TrProjectStage;
use App\Models\TrSubProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyTaskController extends Controller
{
    public function index(Request $request): View
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        $query = TrDailyTask::with([
            'user.subDepartment',
            'project.projectType',
            'projectType',
            'subProject',
            'stage',
        ])->orderBy('dtmTaskDate', 'desc');

        if ($authUser && ! $authUser->isSuperadmin()) {
            $query->where('intUser_ID', $authUser->intUser_ID);
        } elseif ($request->filled('employee')) {
            $query->where('intUser_ID', $request->employee);
        }

        if ($request->filled('project_type')) {
            $query->where(function ($q) use ($request) {
                $q->where('intProjectType_ID', $request->project_type)
                    ->orWhereHas('project', function ($pq) use ($request) {
                        $pq->where('intProjectType_ID', $request->project_type);
                    });
            });
        }

        if ($request->filled('project')) {
            $query->where('intProject_ID', $request->project);
        }

        if ($request->filled('sub_project')) {
            $query->where('intSubProject_ID', $request->sub_project);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('dtmTaskDate', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('dtmTaskDate', '<=', $request->end_date);
        }

        $tasks = $query->get();

        if ($authUser && ! $authUser->isSuperadmin()) {
            $employees = MUser::active()->where('intUser_ID', $authUser->intUser_ID)->get();
            $projectsQuery = MProject::with([
                'projectType',
                'directStages',
                'subProjects.stages',
                'subProjects.assignments.user',
                'assignments.user',
                'user',
            ])
                ->where('bitActive', true)
                ->forUser($authUser->intUser_ID);
        } else {
            $employees = MUser::active()->where('txtRole', '!=', 'Superadmin')->orderBy('txtEmployeeName')->get();
            $projectsQuery = MProject::with([
                'projectType',
                'directStages',
                'subProjects.stages',
                'subProjects.assignments.user',
                'assignments.user',
                'user',
            ])->where('bitActive', true);

            if ($request->filled('employee')) {
                $projectsQuery->forUser($request->employee);
            }
        }

        $allProjects = (clone $projectsQuery)
            ->orderBy('txtProjectName')
            ->get();

        $availableTypeIds = $allProjects->pluck('intProjectType_ID')->filter()->unique();
        $projectTypes = MProjectType::active()
            ->when($availableTypeIds->isNotEmpty(), function ($q) use ($availableTypeIds) {
                $q->whereIn('intProjectType_ID', $availableTypeIds);
            })
            ->orderBy('txtProjectTypeName')
            ->get();

        $filterProjects = $allProjects;
        if ($request->filled('project_type')) {
            $filterProjects = $allProjects->where('intProjectType_ID', (int) $request->project_type)->values();
        }

        $filterProjectIds = $filterProjects->pluck('intProject_ID');
        $subProjectsQuery = TrSubProject::whereIn('intProject_ID', $filterProjectIds);
        if ($request->filled('project')) {
            $subProjectsQuery->where('intProject_ID', $request->project);
        }
        $subProjects = $subProjectsQuery->orderBy('txtSubProjectName')->get();

        $handsontableData = $tasks->map(fn(TrDailyTask $task) => [
            'id' => $task->intDailyTask_ID,
            'date' => $task->dtmTaskDate?->format('Y-m-d'),
            'employeeId' => $task->intUser_ID,
            'employeeName' => $task->user?->txtEmployeeName,
            'projectTypeId' => $task->intProjectType_ID ?: $task->project?->intProjectType_ID,
            'projectTypeName' => $task->projectType?->txtProjectTypeName ?: $task->project?->projectType?->txtProjectTypeName,
            'projectId' => $task->intProject_ID,
            'projectName' => $task->project?->txtProjectName,
            'subProjectId' => $task->intSubProject_ID,
            'subProjectName' => $task->subProject?->txtSubProjectName,
            'stageId' => $task->intProjectStage_ID,
            'stageStep' => $task->stage?->txtProjectStageStep,
            'activity' => $task->txtActivityDescription,
            'deliverable' => $task->txtDeliverableOutput,
            'duration' => (float) $task->floatDurationHours,
            'progress' => (float) $task->floatProgressPercent,
            'status' => $task->txtTaskStatus,
            'notes' => $task->txtNotes,
            'attachment' => $task->txtAttachmentPath ? [
                'path' => $task->txtAttachmentPath,
                'name' => $task->txtAttachmentName,
                'type' => $task->txtAttachmentType,
                'url' => route('reports.daily-tasks.attachment.view', $task->intDailyTask_ID),
            ] : null,
        ]);

        $projectsLookup = $allProjects->map(function (MProject $p) use ($authUserId) {
            $associatedUserNames = collect([$p->user?->txtEmployeeName])
                ->concat($p->assignments->pluck('user.txtEmployeeName'))
                ->concat($p->subProjects->flatMap->assignments->pluck('user.txtEmployeeName'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $associatedUserIds = collect([$p->intUser_ID])
                ->concat($p->assignments->pluck('intUser_ID'))
                ->concat($p->subProjects->flatMap->assignments->pluck('intUser_ID'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'id' => $p->intProject_ID,
                'name' => $p->txtProjectName,
                'code' => $p->txtProjectCode,
                'projectTypeId' => $p->intProjectType_ID,
                'projectTypeName' => $p->projectType?->txtProjectTypeName,
                'userId' => $p->intUser_ID,
                'userName' => $p->user?->txtEmployeeName,
                'associatedUserNames' => $associatedUserNames,
                'associatedUserIds' => $associatedUserIds,
                'isMine' => in_array($authUserId, $associatedUserIds),
                'hasSubProjects' => (bool) $p->bitHasSubProject,
                'directStages' => $p->directStages->map(fn(TrProjectStage $s) => [
                    'id' => $s->intProjectStage_ID,
                    'step' => $s->txtProjectStageStep,
                    'number' => $s->intProjectStageNumber,
                ]),
                'subProjects' => $p->subProjects->map(fn(TrSubProject $sp) => [
                    'id' => $sp->intSubProject_ID,
                    'name' => $sp->txtSubProjectName,
                    'stages' => $sp->stages->map(fn(TrProjectStage $s) => [
                        'id' => $s->intProjectStage_ID,
                        'step' => $s->txtProjectStageStep,
                        'number' => $s->intProjectStageNumber,
                    ]),
                ]),
            ];
        });

        $allActiveProjectTypes = MProjectType::active()->orderBy('txtProjectTypeName')->get();
        $projectTypesLookup = $allActiveProjectTypes->map(fn(MProjectType $pt) => [
            'id' => $pt->intProjectType_ID,
            'name' => $pt->txtProjectTypeName,
            'code' => $pt->txtProjectTypeCode,
        ]);

        $employeesLookup = $employees->map(fn(MUser $e) => [
            'id' => $e->intUser_ID,
            'name' => $e->txtEmployeeName,
        ]);

        return view('reports.daily-tasks', [
            'tasks' => $tasks,
            'handsontableData' => $handsontableData,
            'employees' => $employees,
            'employeesLookup' => $employeesLookup,
            'projectTypes' => $projectTypes,
            'projectTypesLookup' => $projectTypesLookup,
            'projects' => $filterProjects,
            'projectsLookup' => $projectsLookup,
            'subProjects' => $subProjects,
            'authUser' => $authUser,
            'authUserId' => $authUserId,
        ]);
    }

    public function batchSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.id' => ['nullable'],
            'rows.*.date' => ['nullable', 'date'],
            'rows.*.employeeId' => ['nullable', 'integer'],
            'rows.*.projectTypeId' => ['nullable', 'integer'],
            'rows.*.projectId' => ['nullable', 'integer'],
            'rows.*.subProjectId' => ['nullable', 'integer'],
            'rows.*.stageId' => ['nullable', 'integer'],
            'rows.*.activity' => ['required', 'string', 'max:500'],
            'rows.*.deliverable' => ['nullable', 'string', 'max:500'],
            'rows.*.duration' => ['nullable', 'numeric', 'min:0'],
            'rows.*.progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rows.*.status' => ['nullable', 'string', 'max:50'],
            'rows.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $currentUserId = session('auth_user_id') ?: 1;
        $currentUser = MUser::find($currentUserId);
        $savedTasks = [];
        $savedCount = 0;

        foreach ($validated['rows'] as $index => $row) {
            $taskId = ! empty($row['id']) ? (int) $row['id'] : null;
            $userId = ! empty($row['employeeId']) ? (int) $row['employeeId'] : $currentUserId;

            if ($currentUser && ! $currentUser->isSuperadmin()) {
                $userId = $currentUser->intUser_ID;
            }

            $projectId = ! empty($row['projectId']) ? (int) $row['projectId'] : null;
            $projectTypeId = ! empty($row['projectTypeId']) ? (int) $row['projectTypeId'] : null;
            if (! $projectTypeId && $projectId) {
                $project = MProject::find($projectId);
                $projectTypeId = $project?->intProjectType_ID;
            }

            $data = [
                'intUser_ID' => $userId,
                'intProjectType_ID' => $projectTypeId,
                'intProject_ID' => $projectId,
                'intSubProject_ID' => ! empty($row['subProjectId']) ? (int) $row['subProjectId'] : null,
                'intProjectStage_ID' => ! empty($row['stageId']) ? (int) $row['stageId'] : null,
                'dtmTaskDate' => ! empty($row['date']) ? $row['date'] : now()->toDateString(),
                'txtActivityDescription' => $row['activity'],
                'txtDeliverableOutput' => $row['deliverable'] ?? null,
                'floatDurationHours' => isset($row['duration']) ? (float) $row['duration'] : 1.0,
                'floatProgressPercent' => isset($row['progress']) ? (float) $row['progress'] : 100.0,
                'txtTaskStatus' => ! empty($row['status']) ? $row['status'] : 'Completed',
                'txtNotes' => $row['notes'] ?? null,
                'txtInsertedBy' => $currentUser?->txtEmployeeName ?? 'System',
            ];

            if ($taskId && ($task = TrDailyTask::find($taskId))) {
                if ($currentUser && ! $currentUser->isSuperadmin() && $task->intUser_ID !== $currentUser->intUser_ID) {
                    continue;
                }
                $task->update($data);
                $savedTasks[] = [
                    'clientIndex' => $index,
                    'id' => $task->intDailyTask_ID,
                ];
            } else {
                $createdTask = TrDailyTask::create($data);
                $savedTasks[] = [
                    'clientIndex' => $index,
                    'id' => $createdTask->intDailyTask_ID,
                ];
            }

            $savedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan {$savedCount} baris aktivitas daily task.",
            'savedCount' => $savedCount,
            'tasks' => $savedTasks,
        ]);
    }

    public function uploadAttachment(Request $request, TrDailyTask $dailyTask): JsonResponse
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        if ($authUser && ! $authUser->isSuperadmin() && $dailyTask->intUser_ID !== $authUser->intUser_ID) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin mengunggah lampiran untuk tugas ini.',
            ], 403);
        }

        $request->validate([
            'attachment' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf,mp4,webm,ogg,mov', 'max:51200'],
        ]);

        $file = $request->file('attachment');
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?: '';

        $type = 'image';
        if ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            $type = 'pdf';
        } elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov']) || str_starts_with($mime, 'video/')) {
            $type = 'video';
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) || str_starts_with($mime, 'image/')) {
            $type = 'image';
        }

        if ($dailyTask->txtAttachmentPath && Storage::disk('public')->exists($dailyTask->txtAttachmentPath)) {
            Storage::disk('public')->delete($dailyTask->txtAttachmentPath);
        }

        $path = $file->store('daily-task-attachments', 'public');

        $dailyTask->update([
            'txtAttachmentPath' => $path,
            'txtAttachmentName' => $file->getClientOriginalName(),
            'txtAttachmentType' => $type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lampiran berhasil diunggah.',
            'attachment' => [
                'path' => $dailyTask->txtAttachmentPath,
                'name' => $dailyTask->txtAttachmentName,
                'type' => $dailyTask->txtAttachmentType,
                'url' => route('reports.daily-tasks.attachment.view', $dailyTask->intDailyTask_ID),
            ],
        ]);
    }

    public function viewAttachment(TrDailyTask $dailyTask): Response
    {
        if (! $dailyTask->txtAttachmentPath || ! Storage::disk('public')->exists($dailyTask->txtAttachmentPath)) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        return Storage::disk('public')->response(
            $dailyTask->txtAttachmentPath,
            $dailyTask->txtAttachmentName,
            [
                'Content-Disposition' => 'inline; filename="' . addslashes($dailyTask->txtAttachmentName ?? 'attachment') . '"',
            ]
        );
    }

    public function deleteAttachment(TrDailyTask $dailyTask): JsonResponse
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        if ($authUser && ! $authUser->isSuperadmin() && $dailyTask->intUser_ID !== $authUser->intUser_ID) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin menghapus lampiran ini.',
            ], 403);
        }

        if ($dailyTask->txtAttachmentPath && Storage::disk('public')->exists($dailyTask->txtAttachmentPath)) {
            Storage::disk('public')->delete($dailyTask->txtAttachmentPath);
        }

        $dailyTask->update([
            'txtAttachmentPath' => null,
            'txtAttachmentName' => null,
            'txtAttachmentType' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lampiran berhasil dihapus.',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        $query = TrDailyTask::with([
            'user.subDepartment',
            'project.projectType',
            'projectType',
            'subProject',
            'stage',
        ])->orderBy('dtmTaskDate', 'desc');

        if ($authUser && ! $authUser->isSuperadmin()) {
            $query->where('intUser_ID', $authUser->intUser_ID);
        } elseif ($request->filled('employee')) {
            $query->where('intUser_ID', $request->employee);
        }

        if ($request->filled('project_type')) {
            $query->where(function ($q) use ($request) {
                $q->where('intProjectType_ID', $request->project_type)
                    ->orWhereHas('project', function ($pq) use ($request) {
                        $pq->where('intProjectType_ID', $request->project_type);
                    });
            });
        }

        if ($request->filled('project')) {
            $query->where('intProject_ID', $request->project);
        }

        if ($request->filled('sub_project')) {
            $query->where('intSubProject_ID', $request->sub_project);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('dtmTaskDate', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('dtmTaskDate', '<=', $request->end_date);
        }

        $tasks = $query->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daily Tasks');

        // Header
        $headers = [
            'No',
            'Tanggal',
            'Employee',
            'Sub Department',
            'Tipe Project',
            'Project Utama',
            'Sub Project',
            'Stage',
            'Aktivitas / Task',
            'Deliverable Output',
            'Durasi (Jam)',
            'Progress (%)',
            'Status',
            'Catatan',
            'Lampiran',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF006838');
        $sheet->getStyle('A1:O1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $rowNum = 2;
        foreach ($tasks as $idx => $t) {
            $sheet->fromArray([
                $idx + 1,
                $t->dtmTaskDate?->format('d/m/Y'),
                $t->user?->txtEmployeeName,
                $t->user?->subDepartment?->txtSubDepartmentCode,
                $t->projectType?->txtProjectTypeName ?: $t->project?->projectType?->txtProjectTypeName,
                $t->project?->txtProjectName,
                $t->subProject?->txtSubProjectName,
                $t->stage?->txtProjectStageStep,
                $t->txtActivityDescription,
                $t->txtDeliverableOutput,
                $t->floatDurationHours,
                $t->floatProgressPercent . '%',
                $t->txtTaskStatus,
                $t->txtNotes,
                $t->txtAttachmentName ? ($t->txtAttachmentName . ' (' . strtoupper($t->txtAttachmentType ?? '') . ')') : '-',
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'KMI_Daily_Tasks_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
