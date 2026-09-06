@extends('layouts.app', [
'title' => 'Detail Ad Hoc - ' . $adhoc->txtProjectName,
'pageTitle' => 'DETAIL INISIATIF AD HOC',
'pageSubtitle' => '<span>' . ($adhoc->txtProjectCode ?: 'ADH') . '</span> &bull; <span>' . $adhoc->txtProjectName . '</span>',
])

@section('content')
<div class="space-y-6">

    @php
        $urgency = $adhoc->txtPriority ?: 'Medium';
        $urgencyClasses = match($urgency) {
            'Critical' => 'bg-red-100 text-red-700 border-red-200',
            'High' => 'bg-orange-100 text-orange-800 border-orange-200',
            'Medium' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
        $urgencyIcon = match($urgency) {
            'Critical' => 'fa-solid fa-fire',
            'High' => 'fa-solid fa-triangle-exclamation',
            'Medium' => 'fa-solid fa-circle-check',
            default => 'fa-solid fa-circle-info',
        };

        $startDate = $adhoc->dtmProjectStartDate;
        $endDate = $adhoc->dtmProjectEndDate;
        $daysDuration = $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : null;
        $isExpired = $endDate && $endDate->isPast() && $adhoc->floatActual < 100;
        $status = $adhoc->txtStatus ?: 'In Progress';
        $statusClass = match(strtolower($status)) {
            'completed', 'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'under review' => 'bg-purple-100 text-purple-800 border-purple-200',
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-teal-100 text-teal-800 border-teal-200',
        };
    @endphp

    <!-- Header Card -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200">
                    {{ $adhoc->txtProjectCode ?: 'ADH' }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border {{ $urgencyClasses }}">
                    <i class="{{ $urgencyIcon }} text-[10px]"></i>
                    <span>Prioritas {{ $urgency }}</span>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $statusClass }}">
                    {{ $status }}
                </span>
                @if ($adhoc->txtAdHocCategory)
                <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                    <i class="fa-solid fa-tag text-[10px] text-gray-400 mr-1"></i>{{ $adhoc->txtAdHocCategory }}
                </span>
                @endif
            </div>

            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">{{ $adhoc->txtProjectName }}</h2>

            <p class="text-xs text-gray-500 m-0 flex flex-wrap items-center gap-2">
                <span>Sub Dept: <strong class="text-teal-800">{{ $adhoc->subDepartment?->txtSubDepartmentName ?? 'MDP' }}</strong></span>
                &bull;
                <span>Periode Sementara: <strong class="text-gray-800">{{ $startDate ? $startDate->format('d M Y') : '-' }} s/d {{ $endDate ? $endDate->format('d M Y') : '-' }}</strong></span>
                @if ($daysDuration)
                <span class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-800 font-bold text-[11px]">
                    {{ $daysDuration }} Hari Penanganan
                </span>
                @endif
                @if ($isExpired)
                <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 font-bold text-[11px]">
                    Lewat Batas Waktu
                </span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('reports.daily-tasks', ['project_type' => 5, 'project' => $adhoc->intProject_ID]) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline">
            <a href="{{ route('reports.daily-tasks', ['project_type' => 5, 'project' => $adhoc->intProject_ID]) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline" style="background-color: #059669; color: #ffffff;">
                <i class="fa-solid fa-table-cells"></i>
                <span>Laporkan di Daily Task</span>
            </a>
            <a href="{{ route('adhocs.edit', $adhoc) }}" class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline">
            <a href="{{ route('adhocs.edit', $adhoc) }}" class="px-4 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline" style="background-color: #006838; color: #ffffff;">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Ad Hoc</span>
            </a>
            <a href="{{ route('adhocs.index') }}" class="px-3.5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- 2-Columns Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Tujuan Khusus & Deliverable (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Sasaran & Latar Belakang -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
                <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                    <i class="fa-solid fa-bullseye"></i>
                    <span>Sasaran Tunggal & Latar Belakang Pembentukan</span>
                </h3>

                @if ($adhoc->txtSpecialGoal)
                <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200 text-xs text-teal-950 space-y-1">
                    <span class="font-extrabold uppercase text-[10px] text-teal-800 tracking-wider block">Sasaran Khusus yang Harus Tercapai:</span>
                    <p class="font-bold text-sm text-teal-950 m-0 leading-relaxed">{{ $adhoc->txtSpecialGoal }}</p>
                </div>
                @endif

                @if ($adhoc->txtDescription)
                <div class="text-xs text-gray-700 space-y-1">
                    <span class="font-bold text-gray-500 uppercase text-[10px] tracking-wider block">Latar Belakang / Kondisi Pemicu:</span>
                    <p class="m-0 leading-relaxed bg-gray-50 p-3.5 rounded-2xl border border-gray-100">{{ $adhoc->txtDescription }}</p>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                    <div>
                        <span class="font-bold text-gray-500 uppercase text-[10px] tracking-wider block mb-1">Target Deliverable / Output:</span>
                        <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 font-bold text-xs border border-emerald-200">
                            {{ $adhoc->txtDeliverable ?: 'Belum dispesifikasikan' }}
                        </div>
                    </div>

                    <div>
                        <span class="font-bold text-gray-500 uppercase text-[10px] tracking-wider block mb-1">Bobot Kontribusi Kinerja:</span>
                        <div class="p-3 rounded-xl bg-gray-50 text-gray-800 font-bold text-xs border border-gray-200 flex items-center justify-between">
                            <span>Bobot KPI:</span>
                            <span class="text-teal-800 font-black text-sm">{{ $adhoc->floatWeight }}%</span>
                        </div>
                    </div>
                </div>

                @if ($adhoc->txtTargetSkalaGrade)
                <div class="space-y-1 pt-2">
                    <span class="font-bold text-gray-500 uppercase text-[10px] tracking-wider block">Kriteria Keberhasilan Penanganan (Definition of Done):</span>
                    <pre class="bg-gray-50 p-3 rounded-2xl border border-gray-200 text-xs text-gray-800 font-mono whitespace-pre-wrap m-0">{{ $adhoc->txtTargetSkalaGrade }}</pre>
                </div>
                @endif
            </div>

            <!-- Card: Action Plan Stages -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Rencana Tahapan Aksi (Action Plan)</span>
                        </h3>
                        <p class="text-xs text-gray-500 m-0 mt-0.5">Langkah operasional dalam menyelesaikan sasaran ad hoc ini.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-teal-600 h-2.5 rounded-full transition-all" style="width: {{ min(100, $adhoc->floatActual) }}%"></div>
                        </div>
                        <span class="font-black text-xs text-teal-800">{{ round($adhoc->floatActual) }}%</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 w-12 text-center">No</th>
                                <th class="py-2.5 px-3">Langkah Tindakan</th>
                                <th class="py-2.5 px-3 w-36">Periode</th>
                                <th class="py-2.5 px-3 w-20 text-center">Plan</th>
                                <th class="py-2.5 px-3 w-20 text-center">Actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($adhoc->directStages as $st)
                            <tr class="hover:bg-teal-50/20 transition">
                                <td class="py-3 px-3 text-center font-bold text-gray-400">
                                    {{ $st->intProjectStageNumber }}
                                </td>
                                <td class="py-3 px-3 font-bold text-gray-900">
                                    {{ $st->txtProjectStageStep }}
                                </td>
                                <td class="py-3 px-3 text-gray-600">
                                    {{ $st->dtmProjectStageStartDate?->format('d M') }} - {{ $st->dtmProjectStageEndDate?->format('d M Y') }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-gray-700">
                                    {{ $st->floatProjectStagePlan }}%
                                </td>
                                <td class="py-3 px-3 text-center font-extrabold {{ $st->floatProjectStageActual >= $st->floatProjectStagePlan ? 'text-emerald-700' : 'text-teal-800' }}">
                                    {{ $st->floatProjectStageActual }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 italic">
                                    Belum ada tahapan aksi yang ditentukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card: Daily Tasks Reported for this Ad Hoc -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Laporan Aktivitas Harian (Daily Tasks) Terkait</span>
                        </h3>
                        <p class="text-xs text-gray-500 m-0 mt-0.5">Catatan aktivitas pekerjaan aktual yang diinput oleh tim di spreadsheet daily task.</p>
                    </div>
                    <a href="{{ route('reports.daily-tasks', ['project_type' => 5, 'project' => $adhoc->intProject_ID]) }}" class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 hover:bg-teal-100 font-bold text-xs transition flex items-center gap-1.5 no-underline">
                    <a href="{{ route('reports.daily-tasks', ['project_type' => 5, 'project' => $adhoc->intProject_ID]) }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-[#006838] border border-emerald-300 hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 no-underline" style="background-color: #ecfdf5; color: #006838; border: 1px solid #6ee7b7;">
                        <i class="fa-solid fa-plus"></i>
                        <span>Input Task Baru</span>
                    </a>
                </div>

                @if ($adhoc->dailyTasks->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 w-28">Tanggal</th>
                                <th class="py-2.5 px-3 w-40">Karyawan</th>
                                <th class="py-2.5 px-3">Uraian Pekerjaan</th>
                                <th class="py-2.5 px-3 w-20 text-center">Durasi</th>
                                <th class="py-2.5 px-3 w-20 text-center">Progres</th>
                                <th class="py-2.5 px-3 w-24 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($adhoc->dailyTasks->sortByDesc('dtmTaskDate') as $task)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-2.5 px-3 font-medium text-gray-600">
                                    {{ $task->dtmTaskDate?->format('d M Y') }}
                                </td>
                                <td class="py-2.5 px-3 font-bold text-gray-800">
                                    {{ $task->user?->txtEmployeeName ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <p class="m-0 font-bold text-gray-900">{{ $task->txtActivityDescription }}</p>
                                    @if ($task->txtDeliverableOutput)
                                    <p class="m-0 text-[10px] text-gray-500 mt-0.5">Output: {{ $task->txtDeliverableOutput }}</p>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center font-semibold text-gray-700">
                                    {{ $task->floatDurationHours }} jam
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-teal-800">
                                    {{ $task->floatProgressPercent }}%
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                                        {{ $task->txtTaskStatus }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-8 text-center border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50 space-y-2">
                    <i class="fa-solid fa-table-cells text-2xl text-gray-300"></i>
                    <p class="text-xs text-gray-500 m-0">Belum ada daily task yang dicatat untuk inisiatif Ad Hoc ini.</p>
                    <a href="{{ route('reports.daily-tasks', ['project_type' => 5, 'project' => $adhoc->intProject_ID]) }}" class="inline-flex items-center gap-1.5 text-xs text-teal-700 hover:text-teal-900 font-bold no-underline">
                        <span>Buka spreadsheet dan laporkan daily task</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: PIC & Gugus Tugas (1 Col) -->
        <div class="space-y-6">

            <!-- PIC Utama Card -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider m-0">PIC Utama Penanggung Jawab</h3>
                <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-teal-50/50 border border-teal-200">
                    <div class="w-11 h-11 rounded-xl bg-teal-700 text-white font-extrabold flex items-center justify-center text-sm shadow-xs">
                        {{ strtoupper(substr($adhoc->user?->txtEmployeeName ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm font-extrabold text-gray-900 m-0 truncate">{{ $adhoc->user?->txtEmployeeName ?? '-' }}</h4>
                        <p class="text-[11px] text-teal-800 font-bold m-0">{{ $adhoc->user?->txtRole }} &bull; {{ $adhoc->subDepartment?->txtSubDepartmentCode ?? 'MDP' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tim Gugus Tugas Card -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider m-0">Tim Pelaksana / Anggota</h3>
                    <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-900 text-[10px] font-black">
                        {{ $adhoc->assignments->count() }} Orang
                    </span>
                </div>

                <div class="space-y-2.5">
                    @forelse ($adhoc->assignments as $asg)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 border border-gray-100 transition">
                        <div class="w-8 h-8 rounded-lg bg-gray-200 text-gray-700 font-bold flex items-center justify-center text-xs">
                            {{ strtoupper(substr($asg->user?->txtEmployeeName ?? 'U', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-gray-900 m-0 truncate">{{ $asg->user?->txtEmployeeName ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 m-0">{{ $asg->user?->subDepartment?->txtSubDepartmentCode ?? $asg->user?->txtRole }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 italic text-center py-3 m-0">Tidak ada penugasan anggota tambahan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Meta Data Info Card -->
            <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-3 text-xs">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider m-0">Informasi Teknis</h3>
                <div class="space-y-2 divide-y divide-gray-100 text-[11px]">
                    <div class="flex justify-between py-1.5">
                        <span class="text-gray-500">Departemen:</span>
                        <span class="font-bold text-gray-900">{{ $adhoc->department?->txtDepartmentName ?? 'MDP' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-gray-500">Skillset:</span>
                        <span class="font-bold text-gray-900">{{ $adhoc->skillset?->txtSkillsetName ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-gray-500">Dibuat Oleh:</span>
                        <span class="font-bold text-gray-900">{{ $adhoc->txtInsertedBy }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-gray-500">Tanggal Dibuat:</span>
                        <span class="font-bold text-gray-900">{{ $adhoc->dtmInserted?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

