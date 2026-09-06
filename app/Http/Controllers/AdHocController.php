<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSkillset;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrProjectAssignment;
use App\Models\TrProjectStage;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdHocController extends Controller
{
    /**
     * Get or ensure the Ad Hoc project type ID.
     */
    protected function getAdHocProjectTypeId(): int
    {
        $adHocType = MProjectType::where('txtProjectTypeCode', 'Ad Hoc')
            ->orWhere('txtProjectTypeName', 'like', '%Ad Hoc%')
            ->first();

        return $adHocType ? (int) $adHocType->intProjectType_ID : 5;
    }

    public function index(Request $request): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;
        $adHocTypeId = $this->getAdHocProjectTypeId();

        $query = MProject::with([
            'subDepartment',
            'projectType',
            'skillset',
            'user',
            'supervisor',
            'approvedBy',
            'assignments.user',
            'directStages',
            'dailyTasks',
        ])
            ->where('bitActive', true)
            ->where(function ($q) use ($adHocTypeId) {
                $q->where('bitIsAdHoc', true)
                    ->orWhere('intProjectType_ID', $adHocTypeId);
            });

        if (! $authUser->isSuperadmin()) {
            $query->where('intDepartment_ID', $departmentId);
        }

        // Filters
        if ($request->filled('category')) {
            $query->where('txtAdHocCategory', $request->input('category'));
        }

        if ($request->filled('urgency')) {
            $query->where('txtPriority', $request->input('urgency'));
        }

        if ($request->filled('subdept')) {
            $query->where('intSubDepartment_ID', $request->input('subdept'));
        }

        if ($request->filled('employee')) {
            $query->forUser($request->input('employee'));
        }

        if ($request->filled('status')) {
            $query->where('txtStatus', $request->input('status'));
        }

        if ($request->filled('approval_status')) {
            $query->where('txtApprovalStatus', $request->input('approval_status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('txtProjectName', 'like', "%{$search}%")
                    ->orWhere('txtProjectCode', 'like', "%{$search}%")
                    ->orWhere('txtSpecialGoal', 'like', "%{$search}%")
                    ->orWhere('txtDeliverable', 'like', "%{$search}%")
                    ->orWhere('txtDescription', 'like', "%{$search}%");
            });
        }

        $adhocs = $query->orderByDesc('dtmInserted')->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();

        $summary = [
            'total' => $adhocs->count(),
            'pendingApproval' => $adhocs->where('txtApprovalStatus', 'Pending Approval')->count(),
            'inProgress' => $adhocs->where('txtStatus', 'In Progress')->count(),
            'completed' => $adhocs->filter(fn($a) => in_array($a->txtStatus, ['Completed', 'Resolved']) || $a->floatActual >= 100)->count(),
            'critical' => $adhocs->filter(fn($a) => in_array($a->txtPriority, ['Critical', 'High']))->count(),
            'avgProgress' => $adhocs->count() > 0 ? round($adhocs->avg('floatActual'), 1) : 0,
        ];

        return view('adhocs.index', compact('adhocs', 'subDepartments', 'employees', 'summary', 'authUser'));
    }

    public function create(): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $skillsets = MSkillset::where('bitActive', true)->orderBy('txtSkillsetName')->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();
        $supervisors = MUser::where('bitActive', true)
            ->whereIn('txtRole', ['Supervisor', 'Head', 'Superadmin'])
            ->where(function ($q) use ($departmentId) {
                $q->where('intDepartment_ID', $departmentId)
                    ->orWhere('txtRole', 'Superadmin');
            })
            ->orderBy('txtEmployeeName')
            ->get();

        return view('adhocs.create', compact('skillsets', 'subDepartments', 'employees', 'supervisors', 'authUser'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        $adHocTypeId = $this->getAdHocProjectTypeId();

        $validated = $request->validate([
            'txtProjectName' => ['required', 'string', 'max:255'],
            'txtAdHocCategory' => ['required', 'string', 'max:100'],
            'txtPriority' => ['required', 'string', 'max:50'],
            'txtSpecialGoal' => ['required', 'string'],
            'txtDescription' => ['nullable', 'string'],
            'intSkillset_ID' => ['nullable', 'exists:mSkillset,intSkillset_ID'],
            'intSubDepartment_ID' => ['nullable', 'exists:mSubDepartment,intSubDepartment_ID'],
            'intUser_ID' => ['nullable', 'exists:mUser,intUser_ID'],
            'intSupervisor_ID' => ['nullable', 'exists:mUser,intUser_ID'],
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['nullable', 'integer', 'exists:mUser,intUser_ID'],
            'dtmProjectStartDate' => ['required', 'date'],
            'dtmProjectEndDate' => ['required', 'date', 'after_or_equal:dtmProjectStartDate'],
            'txtDeliverable' => ['nullable', 'string'],
            'txtTargetSkalaGrade' => ['nullable', 'string'],
            'floatWeight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'txtStatus' => ['nullable', 'string'],
            'txtApprovalStatus' => ['nullable', 'string'],
            'txtApprovalNotes' => ['nullable', 'string'],
            // Action plan steps
            'stages' => ['nullable', 'array'],
            'stages.*.step' => ['nullable', 'string', 'max:255'],
            'stages.*.start' => ['nullable', 'date'],
            'stages.*.end' => ['nullable', 'date'],
            'stages.*.plan' => ['nullable', 'numeric', 'min:0'],
            'stages.*.actual' => ['nullable', 'numeric', 'min:0'],
        ]);

        $now = now();
        $firstAssigned = ! empty($validated['assignments'][0]) ? (int) $validated['assignments'][0] : null;
        $intUserId = ! empty($validated['intUser_ID']) ? (int) $validated['intUser_ID'] : ($firstAssigned ?: ($authUser?->intUser_ID ?: 1));

        // Detect or resolve designated supervisor
        $supervisorId = ! empty($validated['intSupervisor_ID']) ? (int) $validated['intSupervisor_ID'] : null;
        if (! $supervisorId && ! empty($validated['intSubDepartment_ID'])) {
            $subDept = MSubDepartment::find($validated['intSubDepartment_ID']);
            $supervisorId = $subDept?->supervisors()->where('bitActive', true)->first()?->intUser_ID;
        }
        if (! $supervisorId) {
            $supervisorId = MUser::where('intDepartment_ID', $authUser?->intDepartment_ID ?: 1)
                ->where('txtRole', 'Supervisor')
                ->where('bitActive', true)
                ->first()?->intUser_ID;
        }

        $isEmployee = $authUser && $authUser->isEmployee();
        $approvalStatus = 'Pending Approval';
        if (! $isEmployee && ($request->input('txtApprovalStatus') === 'Approved' || (! $supervisorId || $supervisorId === $authUser?->intUser_ID))) {
            $approvalStatus = $request->input('txtApprovalStatus', 'Approved');
        } elseif (! $isEmployee && $request->filled('txtApprovalStatus')) {
            $approvalStatus = $request->input('txtApprovalStatus');
        }

        $createdProject = null;

        DB::transaction(function () use ($validated, $authUser, $now, $intUserId, $adHocTypeId, $supervisorId, $approvalStatus, &$createdProject) {
            // Generate Ad Hoc Code: ADH-YYYY-XXX
            $year = date('Y');
            $maxId = (int) (MProject::max('intProject_ID') ?? 0) + 1;
            $adhocCount = MProject::where('txtProjectCode', 'like', "ADH-{$year}-%")->count() + 1;
            $code = 'ADH-' . $year . '-' . str_pad($adhocCount, 3, '0', STR_PAD_LEFT);

            $project = MProject::create([
                'intDepartment_ID' => $authUser->intDepartment_ID ?: 1,
                'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? $authUser->intSubDepartment_ID,
                'intProjectType_ID' => $adHocTypeId,
                'intSkillset_ID' => $validated['intSkillset_ID'] ?? null,
                'intUser_ID' => $intUserId,
                'intSupervisor_ID' => $supervisorId,
                'txtApprovalStatus' => $approvalStatus,
                'intApprovedBy_ID' => $approvalStatus === 'Approved' ? $authUser?->intUser_ID : null,
                'dtmApprovedAt' => $approvalStatus === 'Approved' ? $now : null,
                'txtApprovalNotes' => $validated['txtApprovalNotes'] ?? null,
                'txtProjectCode' => $code,
                'txtProjectName' => $validated['txtProjectName'],
                'txtKpiLevel' => 'Ad Hoc',
                'txtDeliverable' => $validated['txtDeliverable'] ?? null,
                'txtTargetSkalaGrade' => $validated['txtTargetSkalaGrade'] ?? null,
                'floatWeight' => isset($validated['floatWeight']) ? (float) $validated['floatWeight'] : 10.0,
                'bitHasSubProject' => false,
                'bitIsAdHoc' => true,
                'txtAdHocCategory' => $validated['txtAdHocCategory'],
                'txtPriority' => $validated['txtPriority'],
                'txtSpecialGoal' => $validated['txtSpecialGoal'],
                'txtDescription' => $validated['txtDescription'] ?? null,
                'dtmProjectStartDate' => $validated['dtmProjectStartDate'],
                'dtmProjectEndDate' => $validated['dtmProjectEndDate'],
                'floatPlan' => 100,
                'floatActual' => 0,
                'txtStatus' => $validated['txtStatus'] ?? 'In Progress',
                'txtStatus' => $approvalStatus === 'Approved' ? ($validated['txtStatus'] ?? 'In Progress') : 'Pending',
                'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // Direct Assignments (Task force team members)
            if (! empty($validated['assignments'])) {
                foreach (array_unique($validated['assignments']) as $assigneeId) {
                    if (! empty($assigneeId)) {
                        TrProjectAssignment::create([
                            'intProject_ID' => $project->intProject_ID,
                            'intSubProject_ID' => null,
                            'intUser_ID' => (int) $assigneeId,
                            'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                            'dtmInserted' => $now,
                        ]);
                    }
                }
            }

            // Direct Action Steps / Stages
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

            $project->recalculateProgress();
            $createdProject = $project;
        });

        // WhatsApp notification to supervisor if pending approval
        if ($createdProject && $createdProject->isPendingApproval() && $createdProject->intSupervisor_ID) {
            $supervisor = MUser::find($createdProject->intSupervisor_ID);
            if ($supervisor && ! empty($supervisor->txtPhone)) {
                $adhocCode = $createdProject->txtProjectCode;
                $adhocName = $createdProject->txtProjectName;
                $creatorName = $authUser?->txtEmployeeName ?? 'Employee';
                $urgency = $createdProject->txtPriority ?? 'Normal';
                $goal = $createdProject->txtSpecialGoal;
                $url = route('adhocs.show', $createdProject);

                $msg = "📢 *NOTIFIKASI PENGAJUAN AD HOC BARU*\n\n"
                    . "Halo Bapak/Ibu *{$supervisor->txtEmployeeName}*,\n"
                    . "Terdapat pengajuan inisiatif Ad Hoc baru yang memerlukan *persetujuan (ACC)* Anda:\n\n"
                    . "🔖 *Kode:* {$adhocCode}\n"
                    . "📌 *Judul:* {$adhocName}\n"
                    . "⚡ *Prioritas:* {$urgency}\n"
                    . "👤 *Diajukan Oleh:* {$creatorName}\n"
                    . "🎯 *Sasaran Khusus:* {$goal}\n"
                    . "📅 *Periode:* " . ($createdProject->dtmProjectStartDate?->format('d/m/Y') ?? '-') . " s/d " . ($createdProject->dtmProjectEndDate?->format('d/m/Y') ?? '-') . "\n\n"
                    . "Mohon periksa dan lakukan ACC melalui tautan berikut:\n"
                    . "👉 {$url}\n\n"
                    . "_Notifikasi otomatis dari Sistem KMI Activity Plan_";

                try {
                    WhatsAppService::sendMessage(
                        phoneNumber: $supervisor->txtPhone,
                        message: $msg,
                        footer: 'KMI Ad Hoc Approval',
                        userId: $supervisor->intUser_ID,
                        recipientName: $supervisor->txtEmployeeName
                    );
                } catch (\Throwable $e) {
                    Log::warning("Gagal mengirim notifikasi WA Ad Hoc ke supervisor {$supervisor->txtPhone}: " . $e->getMessage());
                }
            }
        }

        $successMessage = $approvalStatus === 'Pending Approval'
            ? 'Inisiatif Ad Hoc berhasil diajukan ke Supervisor dan notifikasi WhatsApp telah dikirimkan!'
            : 'Inisiatif Ad Hoc berhasil dibuat dan dijadwalkan!';

        return redirect()->route('adhocs.index')->with('success', $successMessage);
    }

    public function show(MProject $adhoc): View
    {
        $adhoc->load([
            'department',
            'subDepartment',
            'projectType',
            'skillset',
            'user',
            'supervisor',
            'approvedBy',
            'assignments.user',
            'directStages',
            'dailyTasks.user',
        ]);

        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));

        return view('adhocs.show', compact('adhoc', 'authUser'));
    }

    public function edit(MProject $adhoc): View
    {
        $authUser = MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
        $departmentId = $authUser->intDepartment_ID ?: 1;

        $adhoc->load([
            'skillset',
            'supervisor',
            'approvedBy',
            'assignments.user',
            'directStages',
        ]);

        $skillsets = MSkillset::where('bitActive', true)->orderBy('txtSkillsetName')->get();
        $subDepartments = MSubDepartment::where('intDepartment_ID', $departmentId)->where('bitActive', true)->get();
        $employees = MUser::where('bitActive', true)->where('intDepartment_ID', $departmentId)->get();
        $supervisors = MUser::where('bitActive', true)
            ->whereIn('txtRole', ['Supervisor', 'Head', 'Superadmin'])
            ->where(function ($q) use ($departmentId) {
                $q->where('intDepartment_ID', $departmentId)
                    ->orWhere('txtRole', 'Superadmin');
            })
            ->orderBy('txtEmployeeName')
            ->get();

        return view('adhocs.edit', compact('adhoc', 'skillsets', 'subDepartments', 'employees', 'supervisors', 'authUser'));
    }

    public function update(Request $request, MProject $adhoc): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));

        $validated = $request->validate([
            'txtProjectName' => ['required', 'string', 'max:255'],
            'txtAdHocCategory' => ['required', 'string', 'max:100'],
            'txtPriority' => ['required', 'string', 'max:50'],
            'txtSpecialGoal' => ['required', 'string'],
            'txtDescription' => ['nullable', 'string'],
            'intSkillset_ID' => ['nullable', 'exists:mSkillset,intSkillset_ID'],
            'intSubDepartment_ID' => ['nullable', 'exists:mSubDepartment,intSubDepartment_ID'],
            'intUser_ID' => ['nullable', 'exists:mUser,intUser_ID'],
            'intSupervisor_ID' => ['nullable', 'exists:mUser,intUser_ID'],
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['nullable', 'integer', 'exists:mUser,intUser_ID'],
            'dtmProjectStartDate' => ['required', 'date'],
            'dtmProjectEndDate' => ['required', 'date', 'after_or_equal:dtmProjectStartDate'],
            'txtDeliverable' => ['nullable', 'string'],
            'txtTargetSkalaGrade' => ['nullable', 'string'],
            'floatWeight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'txtStatus' => ['nullable', 'string'],
            'stages' => ['nullable', 'array'],
            'stages.*.step' => ['nullable', 'string', 'max:255'],
            'stages.*.start' => ['nullable', 'date'],
            'stages.*.end' => ['nullable', 'date'],
            'stages.*.plan' => ['nullable', 'numeric', 'min:0'],
            'stages.*.actual' => ['nullable', 'numeric', 'min:0'],
        ]);

        $now = now();
        $firstAssigned = ! empty($validated['assignments'][0]) ? (int) $validated['assignments'][0] : null;
        $intUserId = ! empty($validated['intUser_ID']) ? (int) $validated['intUser_ID'] : ($firstAssigned ?: $adhoc->intUser_ID);

        DB::transaction(function () use ($adhoc, $validated, $authUser, $now, $intUserId) {
            $adhoc->update([
                'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? $adhoc->intSubDepartment_ID,
                'intSkillset_ID' => $validated['intSkillset_ID'] ?? null,
                'intUser_ID' => $intUserId,
                'intSupervisor_ID' => $validated['intSupervisor_ID'] ?? $adhoc->intSupervisor_ID,
                'txtProjectName' => $validated['txtProjectName'],
                'txtAdHocCategory' => $validated['txtAdHocCategory'],
                'txtPriority' => $validated['txtPriority'],
                'txtSpecialGoal' => $validated['txtSpecialGoal'],
                'txtDescription' => $validated['txtDescription'] ?? null,
                'txtDeliverable' => $validated['txtDeliverable'] ?? null,
                'txtTargetSkalaGrade' => $validated['txtTargetSkalaGrade'] ?? null,
                'floatWeight' => isset($validated['floatWeight']) ? (float) $validated['floatWeight'] : $adhoc->floatWeight,
                'dtmProjectStartDate' => $validated['dtmProjectStartDate'],
                'dtmProjectEndDate' => $validated['dtmProjectEndDate'],
                'txtStatus' => $validated['txtStatus'] ?? $adhoc->txtStatus,
                'txtUpdatedBy' => $authUser->txtEmail ?? 'system',
                'dtmUpdated' => $now,
            ]);

            // Sync Direct Assignments
            TrProjectAssignment::where('intProject_ID', $adhoc->intProject_ID)->whereNull('intSubProject_ID')->delete();
            if (! empty($validated['assignments'])) {
                foreach (array_unique($validated['assignments']) as $assigneeId) {
                    if (! empty($assigneeId)) {
                        TrProjectAssignment::create([
                            'intProject_ID' => $adhoc->intProject_ID,
                            'intSubProject_ID' => null,
                            'intUser_ID' => (int) $assigneeId,
                            'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                            'dtmInserted' => $now,
                        ]);
                    }
                }
            }

            // Sync Direct Stages
            TrProjectStage::where('intProject_ID', $adhoc->intProject_ID)->whereNull('intSubProject_ID')->delete();
            if (! empty($validated['stages'])) {
                foreach ($validated['stages'] as $sIdx => $stData) {
                    if (empty($stData['step'])) {
                        continue;
                    }
                    TrProjectStage::create([
                        'intProject_ID' => $adhoc->intProject_ID,
                        'intSubProject_ID' => null,
                        'intProjectStageNumber' => $sIdx + 1,
                        'txtProjectStageStep' => $stData['step'],
                        'dtmProjectStageStartDate' => $stData['start'] ?? $adhoc->dtmProjectStartDate,
                        'dtmProjectStageEndDate' => $stData['end'] ?? $adhoc->dtmProjectEndDate,
                        'floatProjectStagePlan' => (float) ($stData['plan'] ?? 0),
                        'floatProjectStageActual' => (float) ($stData['actual'] ?? 0),
                        'txtInsertedBy' => $authUser->txtEmail ?? 'system',
                        'dtmInserted' => $now,
                    ]);
                }
            }

            $adhoc->recalculateProgress();
        });

        return redirect()->route('adhocs.index')->with('success', 'Inisiatif Ad Hoc berhasil diperbarui!');
    }

    public function destroy(MProject $adhoc): RedirectResponse
    {
        $adhoc->update([
            'bitActive' => false,
            'txtUpdatedBy' => session('auth_user_name') ?? 'system',
            'dtmUpdated' => now(),
        ]);

        return redirect()->route('adhocs.index')->with('success', 'Inisiatif Ad Hoc berhasil dinonaktifkan/dihapus.');
    }

    public function approve(Request $request, MProject $adhoc): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        if (! $authUser || ! $authUser->canApproveAdHoc($adhoc)) {
            abort(403, 'Anda tidak memiliki wewenang untuk menyetujui (ACC) inisiatif Ad Hoc ini.');
        }

        $now = now();
        $notes = $request->input('txtApprovalNotes');

        $adhoc->update([
            'txtApprovalStatus' => 'Approved',
            'txtStatus' => 'In Progress',
            'intApprovedBy_ID' => $authUser->intUser_ID,
            'dtmApprovedAt' => $now,
            'txtApprovalNotes' => $notes ?: $adhoc->txtApprovalNotes,
            'txtUpdatedBy' => $authUser->txtEmail ?? 'system',
            'dtmUpdated' => $now,
        ]);

        // Optional notification to creator
        $creator = $adhoc->user;
        if ($creator && ! empty($creator->txtPhone) && $creator->intUser_ID !== $authUser->intUser_ID) {
            $msg = "✅ *INISIATIF AD HOC DISETUJUI (ACC)*\n\n"
                . "Halo *{$creator->txtEmployeeName}*,\n"
                . "Pengajuan inisiatif Ad Hoc Anda telah *DISETUJUI (ACC)* oleh *{$authUser->txtEmployeeName}*:\n\n"
                . "🔖 *Kode:* {$adhoc->txtProjectCode}\n"
                . "📌 *Judul:* {$adhoc->txtProjectName}\n"
                . ($notes ? "💬 *Catatan Supervisor:* {$notes}\n" : "")
                . "🚀 Status inisiatif kini *In Progress* dan siap dilaksanakan serta dilaporkan pada Daily Tasks.\n\n"
                . "👉 " . route('adhocs.show', $adhoc);

            try {
                WhatsAppService::sendMessage(
                    phoneNumber: $creator->txtPhone,
                    message: $msg,
                    footer: 'KMI Ad Hoc Approved',
                    userId: $creator->intUser_ID,
                    recipientName: $creator->txtEmployeeName
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal kirim WA approval konfirmasi: " . $e->getMessage());
            }
        }

        return redirect()->route('adhocs.show', $adhoc)->with('success', 'Inisiatif Ad Hoc berhasil disetujui (ACC) dan status kini In Progress!');
    }

    public function reject(Request $request, MProject $adhoc): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        if (! $authUser || ! $authUser->canApproveAdHoc($adhoc)) {
            abort(403, 'Anda tidak memiliki wewenang untuk menolak inisiatif Ad Hoc ini.');
        }

        $request->validate([
            'txtApprovalNotes' => ['required', 'string', 'max:1000'],
        ], [
            'txtApprovalNotes.required' => 'Mohon berikan alasan atau catatan revisi penolakan.',
        ]);

        $now = now();
        $notes = $request->input('txtApprovalNotes');

        $adhoc->update([
            'txtApprovalStatus' => 'Rejected',
            'txtStatus' => 'Pending',
            'intApprovedBy_ID' => $authUser->intUser_ID,
            'dtmApprovedAt' => $now,
            'txtApprovalNotes' => $notes,
            'txtUpdatedBy' => $authUser->txtEmail ?? 'system',
            'dtmUpdated' => $now,
        ]);

        // Notify creator via WA
        $creator = $adhoc->user;
        if ($creator && ! empty($creator->txtPhone) && $creator->intUser_ID !== $authUser->intUser_ID) {
            $msg = "⚠️ *INISIATIF AD HOC PERLU REVISI / DITOLAK*\n\n"
                . "Halo *{$creator->txtEmployeeName}*,\n"
                . "Pengajuan inisiatif Ad Hoc Anda memerlukan revisi atau ditolak oleh *{$authUser->txtEmployeeName}*:\n\n"
                . "🔖 *Kode:* {$adhoc->txtProjectCode}\n"
                . "📌 *Judul:* {$adhoc->txtProjectName}\n"
                . "📝 *Alasan / Catatan Revisi:* {$notes}\n\n"
                . "Silakan periksa dan perbaiki pengajuan melalui tautan berikut:\n"
                . "👉 " . route('adhocs.edit', $adhoc);

            try {
                WhatsAppService::sendMessage(
                    phoneNumber: $creator->txtPhone,
                    message: $msg,
                    footer: 'KMI Ad Hoc Revision',
                    userId: $creator->intUser_ID,
                    recipientName: $creator->txtEmployeeName
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal kirim WA reject konfirmasi: " . $e->getMessage());
            }
        }

        return redirect()->route('adhocs.show', $adhoc)->with('error', 'Inisiatif Ad Hoc telah ditolak / dikembalikan untuk revisi.');
    }
}

