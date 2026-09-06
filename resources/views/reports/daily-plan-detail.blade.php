@extends('layouts.app', [
'title' => $dailyPlan->txtWeekTitle . ' - Daily Activity Plan',
'pageTitle' => 'JADWAL AKTIVITAS MINGGUAN',
'pageSubtitle' => '<span>' . $dailyPlan->txtWeekTitle . '</span> &bull; <span>' . $dailyPlan->user?->txtEmployeeName . '</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Plan Header Card -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">{{ $dailyPlan->txtWeekTitle }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ $dailyPlan->txtStatus }}
                </span>
            </div>
            <p class="text-xs text-gray-500 m-0">
                PIC: <strong class="text-gray-800">{{ $dailyPlan->user?->txtEmployeeName }}</strong> &bull; Sub Dept: <strong class="text-emerald-700">{{ $dailyPlan->user?->subDepartment?->txtSubDepartmentCode ?? 'MDP' }}</strong> &bull; Periode: {{ $dailyPlan->dtmWeekStartDate?->format('d M') }} s/d {{ $dailyPlan->dtmWeekEndDate?->format('d M Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('reports.daily-plans') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Daftar Week</span>
            </a>
        </div>
    </div>

    @if ($dailyPlan->txtTargetGoals)
    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-2.5">
        <i class="fa-solid fa-bullseye text-[#006838] text-base shrink-0 mt-0.5"></i>
        <div>
            <strong>Target / Fokus Utama Minggu Ini:</strong>
            <p class="m-0 mt-0.5 text-emerald-800">{{ $dailyPlan->txtTargetGoals }}</p>
        </div>
    </div>
    @endif

    <!-- Horizontal 5 Days Columns Grid (Senin - Jumat) matching PDF reference -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start">
        @php
        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        @endphp

        @foreach ($dayOrder as $dayName)
        @php
        $dayDate = $days[$dayName] ?? null;
        $dayActivities = $activitiesByDay[$dayName] ?? collect();
        @endphp

        <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col justify-between overflow-hidden min-h-[480px]">

            <!-- Day Column Header -->
            <div class="p-4 bg-gradient-to-r from-[#006838] to-[#004d29] text-white">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black tracking-wider uppercase">{{ $dayName }}</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-emerald-200">
                        {{ $dayActivities->count() }} Kegiatan
                    </span>
                </div>
                <p class="text-[11px] text-emerald-100 font-medium m-0 mt-1">
                    {{ $dayDate ? $dayDate->format('d M Y') : '-' }}
                </p>
            </div>

            <!-- Activities List for This Day -->
            <div class="p-3 space-y-2.5 flex-1 overflow-y-auto max-h-[420px] custom-scrollbar">
                @forelse ($dayActivities as $act)
                <div class="p-3 rounded-2xl bg-gray-50 hover:bg-emerald-50/40 border border-gray-200 hover:border-emerald-300 transition text-xs space-y-1.5 relative group">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[11px] font-extrabold text-[#006838] flex items-center gap-1">
                            <i class="fa-regular fa-clock text-[10px]"></i>
                            {{ $act->txtStartTime }} - {{ $act->txtEndTime }}
                        </span>
                        <span class="text-[10px] font-bold text-gray-400 bg-white px-1.5 py-0.5 rounded-md border border-gray-200">
                            {{ $act->floatDuration }}j
                        </span>
                    </div>

                    <p class="font-bold text-gray-900 m-0 leading-tight">{{ $act->txtActivityName }}</p>

                    @if ($act->project)
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60 w-fit max-w-full">
                            <i class="fa-solid fa-diagram-project text-[9px] shrink-0"></i>
                            <span class="truncate">{{ $act->project->txtProjectName }}</span>
                        </div>
                        @if ($act->subProject)
                        <div class="flex items-center gap-1 text-[9.5px] text-teal-700 font-medium bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200/60 w-fit max-w-full">
                            <i class="fa-solid fa-folder-tree text-[8.5px] shrink-0"></i>
                            <span class="truncate">{{ $act->subProject->txtSubProjectName }}</span>
                        </div>
                        @endif
                        @if ($act->stage)
                        <div class="flex items-center gap-1 text-[9.5px] text-blue-700 font-medium bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60 w-fit max-w-full">
                            <i class="fa-solid fa-list-check text-[8.5px] shrink-0"></i>
                            <span class="truncate">Stage: {{ $act->stage->txtProjectStageStep }}</span>
                        </div>
                        @endif
                    </div>
                    @elseif ($act->subProject || $act->stage)
                    <div class="space-y-1">
                        @if ($act->subProject)
                        <div class="flex items-center gap-1 text-[9.5px] text-teal-700 font-medium bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200/60 w-fit max-w-full">
                            <i class="fa-solid fa-folder-tree text-[8.5px] shrink-0"></i>
                            <span class="truncate">{{ $act->subProject->txtSubProjectName }}</span>
                        </div>
                        @endif
                        @if ($act->stage)
                        <div class="flex items-center gap-1 text-[9.5px] text-blue-700 font-medium bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60 w-fit max-w-full">
                            <i class="fa-solid fa-list-check text-[8.5px] shrink-0"></i>
                            <span class="truncate">Stage: {{ $act->stage->txtProjectStageStep }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-1 text-[10px] text-gray-500">
                        @if ($act->txtLocationType)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                            {{ $act->txtLocationType }}
                        </span>
                        @else
                        <span>-</span>
                        @endif

                        <div class="flex items-center gap-1">
                            <button type="button" onclick="openEditActivityModal({{ json_encode([
                                'id' => $act->intDailyPlanActivity_ID,
                                'day' => $act->txtDayName,
                                'date' => $act->dtmActivityDate?->format('Y-m-d'),
                                'name' => $act->txtActivityName,
                                'startTime' => $act->txtStartTime,
                                'endTime' => $act->txtEndTime,
                                'duration' => $act->floatDuration,
                                'location' => $act->txtLocationType,
                                'projectId' => $act->intProject_ID,
                                'subProjectId' => $act->intSubProject_ID,
                                'stageId' => $act->intProjectStage_ID,
                                'remarks' => $act->txtRemarks,
                            ]) }})" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-emerald-600 transition p-1 cursor-pointer" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                            </button>
                            <form action="{{ route('reports.daily-plans.activities.destroy', $act) }}" method="POST" onsubmit="return confirm('Hapus aktivitas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition p-1 cursor-pointer" title="Hapus">
                                    <i class="fa-solid fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-gray-300">
                    <i class="fa-solid fa-calendar-day text-2xl mb-1 block"></i>
                    <span class="text-[11px]">Belum ada aktivitas.</span>
                </div>
                @endforelse
            </div>

            <!-- Persistent Add (+) Button Underneath Each Day -->
            <div class="p-3 border-t border-gray-100 bg-gray-50/50">
                <button type="button" onclick="openActivityModal('{{ $dayName }}', '{{ $dayDate ? $dayDate->format('Y-m-d') : '' }}')"
                    class="w-full py-2.5 rounded-xl border-2 border-dashed border-emerald-300 hover:border-[#006838] bg-white hover:bg-emerald-50 text-[#006838] font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add Activity</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>

</div>

<!-- Modal Add / Edit Activity -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="activityModal">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-100 animate-scale-in">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-extrabold text-gray-900 m-0 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-[#006838]"></i>
                <span id="modalDayTitle">Tambah Aktivitas</span>
            </h3>
            <button type="button" onclick="closeActivityModal()" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('reports.daily-plans.activities.store', $dailyPlan) }}" method="POST" class="space-y-4" id="activityForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="txtDayName" id="inputDayName">
            <input type="hidden" name="dtmActivityDate" id="inputActivityDate">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Aktivitas / Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="txtActivityName" id="actActivityName" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                    placeholder="Contoh: SENTUL Plant Ampere Check / Workshop I2MS / Training WD / Cuti">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Start Time <span class="text-red-500">*</span></label>
                    <input type="time" name="txtStartTime" id="actStartTime" value="08:00" required onchange="calcDuration()"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">End Time <span class="text-red-500">*</span></label>
                    <input type="time" name="txtEndTime" id="actEndTime" value="10:00" required onchange="calcDuration()"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Durasi (Jam)</label>
                    <input type="number" step="0.5" name="floatDuration" id="actDuration" value="2.0"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs text-center focus:border-[#006838] outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Lokasi / Tagging</label>
                    <input type="text" name="txtLocationType" id="actLocationType" list="locationOptions"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                        placeholder="Contoh: SENTUL, Mekor, KMI, Cuti, Meeting">
                    <datalist id="locationOptions">
                        <option value="SENTUL">
                        <option value="Mekor">
                        <option value="KMI">
                        <option value="BG Office">
                        <option value="Cuti">
                        <option value="Meeting">
                        <option value="Online">
                        <option value="Workshop">
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-diagram-project text-[#006838] mr-1"></i> Project (Opsional)
                    </label>
                    <select name="intProject_ID" id="inputProjectId" onchange="handleProjectChange()"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                        <option value="">-- Tidak dikaitkan ke Project --</option>
                        @foreach ($projects as $prj)
                        <option value="{{ $prj->intProject_ID }}">
                            {{ $prj->isAdHoc() ? '[Ad Hoc] ' : '' }}{{ $prj->txtProjectName }}
                        </option>
                        @endforeach
                    </select>
                    @if ($projects->isEmpty())
                    <p class="text-[10px] text-gray-400 mt-1 m-0">Employee ini belum memiliki project aktif.</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-folder-tree text-teal-600 mr-1"></i> Sub Project (Opsional)
                    </label>
                    <select name="intSubProject_ID" id="inputSubProjectId" onchange="handleSubProjectChange()"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none disabled:bg-gray-100 disabled:text-gray-400">
                        <option value="">-- Pilih Project terlebih dahulu --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-list-check text-blue-600 mr-1"></i> Stage (Opsional)
                    </label>
                    <select name="intProjectStage_ID" id="inputProjectStageId"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none disabled:bg-gray-100 disabled:text-gray-400">
                        <option value="">-- Pilih Project terlebih dahulu --</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" name="txtRemarks" id="actRemarks"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                    placeholder="Catatan pengerjaan...">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeActivityModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span id="submitBtnText">Simpan Aktivitas</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const projectsData = @json($projectsLookup ?? []);
    const defaultStoreUrl = "{{ route('reports.daily-plans.activities.store', $dailyPlan) }}";

    function handleProjectChange(selectedSubProjectId = null, selectedStageId = null) {
        const prjId = parseInt(document.getElementById('inputProjectId').value, 10);
        const subPrjSelect = document.getElementById('inputSubProjectId');
        const stageSelect = document.getElementById('inputProjectStageId');

        subPrjSelect.innerHTML = '';
        stageSelect.innerHTML = '';

        if (!prjId) {
            subPrjSelect.innerHTML = '<option value="">-- Pilih Project terlebih dahulu --</option>';
            subPrjSelect.disabled = true;
            stageSelect.innerHTML = '<option value="">-- Pilih Project terlebih dahulu --</option>';
            stageSelect.disabled = true;
            return;
        }

        const project = projectsData.find(p => p.id === prjId);
        if (!project) {
            subPrjSelect.innerHTML = '<option value="">-- Tidak ada Sub Project --</option>';
            subPrjSelect.disabled = true;
            stageSelect.innerHTML = '<option value="">-- Tidak ada Stage --</option>';
            stageSelect.disabled = true;
            return;
        }

        // Populate Sub Projects
        if (project.subProjects && project.subProjects.length > 0) {
            subPrjSelect.disabled = false;
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Pilih Sub Project (Opsional) --';
            subPrjSelect.appendChild(defaultOpt);

            project.subProjects.forEach(sp => {
                const opt = document.createElement('option');
                opt.value = sp.id;
                opt.textContent = sp.name;
                if (selectedSubProjectId && String(sp.id) === String(selectedSubProjectId)) {
                    opt.selected = true;
                }
                subPrjSelect.appendChild(opt);
            });
        } else {
            subPrjSelect.disabled = true;
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '(Project tidak memiliki Sub Project)';
            subPrjSelect.appendChild(opt);
        }

        // Trigger sub project change to populate stages
        handleSubProjectChange(selectedStageId);
    }

    function handleSubProjectChange(selectedStageId = null) {
        const prjId = parseInt(document.getElementById('inputProjectId').value, 10);
        const subPrjId = parseInt(document.getElementById('inputSubProjectId').value, 10);
        const stageSelect = document.getElementById('inputProjectStageId');

        stageSelect.innerHTML = '';

        if (!prjId) {
            stageSelect.innerHTML = '<option value="">-- Pilih Project terlebih dahulu --</option>';
            stageSelect.disabled = true;
            return;
        }

        const project = projectsData.find(p => p.id === prjId);
        if (!project) {
            stageSelect.innerHTML = '<option value="">-- Tidak ada Stage --</option>';
            stageSelect.disabled = true;
            return;
        }

        let availableStages = [];

        if (subPrjId) {
            // If sub project is selected, load its stages
            const subPrj = project.subProjects ? project.subProjects.find(sp => sp.id === subPrjId) : null;
            if (subPrj && subPrj.stages && subPrj.stages.length > 0) {
                availableStages = subPrj.stages;
            }
        } else {
            // If no sub project selected, load direct stages of the project
            if (project.directStages && project.directStages.length > 0) {
                availableStages = project.directStages;
            }
        }

        if (availableStages.length > 0) {
            stageSelect.disabled = false;
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Pilih Stage (Opsional) --';
            stageSelect.appendChild(defaultOpt);

            availableStages.forEach(st => {
                const opt = document.createElement('option');
                opt.value = st.id;
                opt.textContent = `Stage ${st.number ? st.number + ': ' : ''}${st.step}`;
                if (selectedStageId && String(st.id) === String(selectedStageId)) {
                    opt.selected = true;
                }
                stageSelect.appendChild(opt);
            });
        } else {
            stageSelect.disabled = true;
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = subPrjId ? '(Sub Project ini tidak memiliki Stage)' : '(Project tidak memiliki Stage langsung)';
            stageSelect.appendChild(opt);
        }
    }

    function openActivityModal(dayName, dateStr) {
        document.getElementById('activityForm').reset();
        document.getElementById('activityForm').action = defaultStoreUrl;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalDayTitle').textContent = `Tambah Aktivitas: Hari ${dayName} (${dateStr})`;
        document.getElementById('submitBtnText').textContent = 'Simpan Aktivitas';
        document.getElementById('inputDayName').value = dayName;
        document.getElementById('inputActivityDate').value = dateStr;
        document.getElementById('actStartTime').value = '08:00';
        document.getElementById('actEndTime').value = '10:00';
        document.getElementById('actDuration').value = '2.0';
        document.getElementById('actLocationType').value = '';
        document.getElementById('actRemarks').value = '';
        document.getElementById('inputProjectId').value = '';
        handleProjectChange();
        document.getElementById('activityModal').classList.remove('hidden');
    }

    function openEditActivityModal(act) {
        document.getElementById('activityForm').reset();
        document.getElementById('activityForm').action = `/reports/daily-plans/activities/${act.id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('modalDayTitle').textContent = `Edit Aktivitas: Hari ${act.day} (${act.date || ''})`;
        document.getElementById('submitBtnText').textContent = 'Simpan Perubahan';
        document.getElementById('inputDayName').value = act.day;
        document.getElementById('inputActivityDate').value = act.date || '';
        document.getElementById('actActivityName').value = act.name || '';
        document.getElementById('actStartTime').value = act.startTime || '08:00';
        document.getElementById('actEndTime').value = act.endTime || '10:00';
        document.getElementById('actDuration').value = act.duration || 2.0;
        document.getElementById('actLocationType').value = act.location || '';
        document.getElementById('actRemarks').value = act.remarks || '';
        document.getElementById('inputProjectId').value = act.projectId || '';
        handleProjectChange(act.subProjectId, act.stageId);
        document.getElementById('activityModal').classList.remove('hidden');
    }

    function closeActivityModal() {
        document.getElementById('activityModal').classList.add('hidden');
    }

    function calcDuration() {
        const st = document.getElementById('actStartTime').value;
        const et = document.getElementById('actEndTime').value;
        if (!st || !et) return;

        const [sh, sm] = st.split(':').map(Number);
        const [eh, em] = et.split(':').map(Number);
        let mins = (eh * 60 + em) - (sh * 60 + sm);
        if (mins > 0) {
            document.getElementById('actDuration').value = (mins / 60).toFixed(1);
        }
    }

    // Initialize dropdowns on page load if project already selected
    document.addEventListener('DOMContentLoaded', function() {
        handleProjectChange();
    });
</script>
@endsection