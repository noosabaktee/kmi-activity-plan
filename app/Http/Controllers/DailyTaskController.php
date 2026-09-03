<?php

namespace App\Http\Controllers;

use App\Models\MProject;
use App\Models\MUser;
use App\Models\TrDailyTask;
use App\Models\TrSubProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyTaskController extends Controller
{
    public function index(Request $request): View
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        $query = TrDailyTask::with(['user', 'project', 'subProject'])->orderBy('dtmTaskDate', 'desc');

        if ($authUser && $authUser->isEmployee()) {
            $query->where('intUser_ID', $authUser->intUser_ID);
        } elseif ($request->filled('employee')) {
            $query->where('intUser_ID', $request->employee);
        }

        if ($request->filled('project')) {
            $query->where('intProject_ID', $request->project);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('dtmTaskDate', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('dtmTaskDate', '<=', $request->end_date);
        }

        $tasks = $query->get();

        if ($authUser && $authUser->isEmployee()) {
            $employees = MUser::active()->where('intUser_ID', $authUser->intUser_ID)->get();
            $projectsQuery = MProject::with(['subProjects.assignments.user', 'assignments.user', 'user'])
                ->where('bitActive', true)
                ->forUser($authUser->intUser_ID);
        } else {
            $employees = MUser::active()->where('txtRole', 'Employee')->orderBy('txtEmployeeName')->get();
            $projectsQuery = MProject::with(['subProjects.assignments.user', 'assignments.user', 'user'])->where('bitActive', true);

            if ($authUser && ! $authUser->isSuperadmin()) {
                $departmentId = $authUser->intDepartment_ID ?: 1;
                $projectsQuery->where('intDepartment_ID', $departmentId);
            }

            if ($request->filled('employee')) {
                $projectsQuery->forUser($request->employee);
            }
        }

        $projects = $projectsQuery
            ->orderBy('txtProjectName')
            ->get();

        $handsontableData = $tasks->map(fn(TrDailyTask $task) => [
            'id' => $task->intDailyTask_ID,
            'date' => $task->dtmTaskDate?->format('Y-m-d'),
            'employeeId' => $task->intUser_ID,
            'employeeName' => $task->user?->txtEmployeeName,
            'projectId' => $task->intProject_ID,
            'projectName' => $task->project?->txtProjectName,
            'subProjectId' => $task->intSubProject_ID,
            'subProjectName' => $task->subProject?->txtSubProjectName,
            'activity' => $task->txtActivityDescription,
            'deliverable' => $task->txtDeliverableOutput,
            'duration' => (float) $task->floatDurationHours,
            'progress' => (float) $task->floatProgressPercent,
            'status' => $task->txtTaskStatus,
            'notes' => $task->txtNotes,
        ]);

        $projectsLookup = $projects->map(fn(MProject $p) => [
            'id' => $p->intProject_ID,
            'name' => $p->txtProjectName,
            'code' => $p->txtProjectCode,
            'userId' => $p->intUser_ID,
            'userName' => $p->user?->txtEmployeeName,
            'isMine' => $p->intUser_ID === $authUserId,
            'subProjects' => $p->subProjects->map(fn(TrSubProject $sp) => [
                'id' => $sp->intSubProject_ID,
                'name' => $sp->txtSubProjectName,
            ]),
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
            'projects' => $projects,
            'projectsLookup' => $projectsLookup,
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
            'rows.*.projectId' => ['nullable', 'integer'],
            'rows.*.subProjectId' => ['nullable', 'integer'],
            'rows.*.activity' => ['required', 'string', 'max:500'],
            'rows.*.deliverable' => ['nullable', 'string', 'max:500'],
            'rows.*.duration' => ['nullable', 'numeric', 'min:0'],
            'rows.*.progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rows.*.status' => ['nullable', 'string', 'max:50'],
            'rows.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $currentUserId = session('auth_user_id') ?: 1;
        $currentUser = MUser::find($currentUserId);
        $savedCount = 0;

        foreach ($validated['rows'] as $row) {
            $taskId = ! empty($row['id']) ? (int) $row['id'] : null;
            $userId = ! empty($row['employeeId']) ? (int) $row['employeeId'] : $currentUserId;

            if ($currentUser && $currentUser->isEmployee()) {
                $userId = $currentUser->intUser_ID;
            }

            $data = [
                'intUser_ID' => $userId,
                'intProject_ID' => ! empty($row['projectId']) ? (int) $row['projectId'] : null,
                'intSubProject_ID' => ! empty($row['subProjectId']) ? (int) $row['subProjectId'] : null,
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
                $task->update($data);
            } else {
                TrDailyTask::create($data);
            }

            $savedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan {$savedCount} baris aktivitas daily task.",
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = TrDailyTask::with(['user', 'project', 'subProject'])->orderBy('dtmTaskDate', 'desc');

        if ($request->filled('employee')) {
            $query->where('intUser_ID', $request->employee);
        }
        if ($request->filled('project')) {
            $query->where('intProject_ID', $request->project);
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
        $headers = ['No', 'Tanggal', 'Employee', 'Sub Department', 'Project Utama', 'Sub Project', 'Aktivitas / Task', 'Deliverable Output', 'Durasi (Jam)', 'Progress (%)', 'Status', 'Catatan'];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF006838');
        $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $rowNum = 2;
        foreach ($tasks as $idx => $t) {
            $sheet->fromArray([
                $idx + 1,
                $t->dtmTaskDate?->format('d/m/Y'),
                $t->user?->txtEmployeeName,
                $t->user?->subDepartment?->txtSubDepartmentCode,
                $t->project?->txtProjectName,
                $t->subProject?->txtSubProjectName,
                $t->txtActivityDescription,
                $t->txtDeliverableOutput,
                $t->floatDurationHours,
                $t->floatProgressPercent . '%',
                $t->txtTaskStatus,
                $t->txtNotes,
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        foreach (range('A', 'L') as $col) {
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
