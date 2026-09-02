@extends('layouts.app', [
'title' => ($user->txtEmployeeName ?? 'Profile') . ' - KMI Activity Plan',
'pageTitle' => 'USER PROFILE',
'pageSubtitle' => '<span>' . ($user->txtEmployeeName ?? 'Employee') . '</span> &bull; <span>' . ($user->txtRole ?? 'Role') . '</span>',
])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- 1. Profile Header Card -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col md:flex-row items-start justify-between gap-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start gap-6 text-left flex-1">
            <!-- Avatar / Initials -->
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#006838] to-[#004d29] text-white font-extrabold text-2xl flex items-center justify-center shadow-md shrink-0 border-2 border-emerald-100">
                {{ strtoupper(substr($user->txtEmployeeName ?? 'U', 0, 2)) }}
            </div>

            <!-- Main Info -->
            <div class="space-y-1.5 flex-1 text-left">
                <div class="flex flex-wrap items-center justify-start gap-2.5">
                    <h2 class="text-xl md:text-2xl font-black text-gray-900 m-0">{{ $user->txtEmployeeName }}</h2>

                    @php
                    $roleColors = [
                    'Employee' => 'bg-emerald-100 text-[#006838] border-emerald-200',
                    'Supervisor' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'Head' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'Superadmin' => 'bg-amber-100 text-amber-800 border-amber-200',
                    ];
                    $badgeClass = $roleColors[$user->txtRole] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                        <i class="fa-solid fa-user-shield text-[10px]"></i>
                        {{ $user->txtRole }}
                    </span>

                    @if ($user->bitActive)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-50 text-green-700 border border-green-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        Aktif
                    </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-start gap-y-1 gap-x-3 text-xs text-gray-500">
                    <span>NIK / Kode: <strong class="text-gray-800">{{ $user->txtEmployeeCode ?: '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>Jabatan: <strong class="text-gray-800">{{ $user->txtPosition ?: 'Staff MDP' }}</strong></span>
                    <span>&bull;</span>
                    <span>Department: <strong class="text-gray-800">{{ $user->department?->txtDepartmentName ?? 'Manufacturing Development & Planning' }}</strong></span>
                </div>

                <div class="pt-1 text-left">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50/80 px-3 py-1 rounded-xl border border-emerald-100">
                        <i class="fa-solid fa-sitemap text-emerald-600"></i>
                        Sub Department: <strong class="text-emerald-950">{{ $user->subDepartment?->txtSubDepartmentName ?? '-' }} ({{ $user->subDepartment?->txtSubDepartmentCode ?? '-' }})</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 self-start md:self-center shrink-0">
            @if (! $isOwnProfile)
            <a href="{{ route('master.index', ['tab' => 'users']) }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            @endif

            <a href="{{ route('profile.edit') }}" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Profile</span>
            </a>
        </div>
    </div>

    <!-- 2. Executive KPI & Activity Performance Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Projects -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Projects</span>
                    <h3 class="text-2xl font-black text-[#006838] mt-1 m-0">{{ $totalProjects }}</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">Bobot: <strong class="text-gray-700">{{ $totalWeight }}%</strong></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-[#EBF5E9] text-[#006838] flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Single / Multi Sub</span>
                <span class="font-bold text-gray-700">{{ $projects->where('bitHasSubProject', true)->count() }} Multi Sub</span>
            </div>
        </div>

        <!-- Realisasi KPI (Avg Actual) -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Realisasi KPI</span>
                    <h3 class="text-2xl font-black text-[#8CC63F] mt-1 m-0">{{ $avgActual }}%</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">Plan: <strong class="text-gray-700">{{ $avgPlan }}%</strong></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-[#8CC63F] flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="mt-3 pt-2 border-t border-gray-100">
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#8CC63F] h-2 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $avgActual)) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Avg KPI Score -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Skor KPI</span>
                    <h3 class="text-2xl font-black text-amber-500 mt-1 m-0">{{ $avgScore }} <span class="text-xs font-semibold text-gray-400">/ 5</span></h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">Target Skala Grade</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center gap-1 text-amber-400 text-xs">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa-solid fa-star {{ $i <= round($avgScore) ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                    <span class="text-[10px] text-gray-400 ml-1 font-semibold">Skala 1-5</span>
            </div>
        </div>

        <!-- Total Jam Daily Task -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jam Kerja Log</span>
                    <h3 class="text-2xl font-black text-[#006838] mt-1 m-0">{{ $totalHoursLogged }} <span class="text-xs font-semibold text-gray-400">Jam</span></h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">{{ $totalDailyTasks }} Daily Tasks</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-[#EBF5E9] text-[#006838] flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Task Selesai</span>
                <span class="font-bold text-emerald-700">{{ $completedDailyTasks }} / {{ $totalDailyTasks }}</span>
            </div>
        </div>

        <!-- Weekly Plan & Activities -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aktivitas Plan</span>
                    <h3 class="text-2xl font-black text-indigo-600 mt-1 m-0">{{ $activityCompletionRate }}%</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">{{ $totalWeeklyPlans }} Weekly Plans</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Aktivitas Selesai</span>
                <span class="font-bold text-indigo-700">{{ $completedActivities }} / {{ $totalActivities }}</span>
            </div>
        </div>
    </div>

    <!-- Supervisor Section (if Supervisor) -->
    @if ($user->isSupervisor())
    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-3xl p-6 text-white shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-blue-500/30 text-blue-200 text-xs font-bold backdrop-blur-xs mb-1">
                    <i class="fa-solid fa-shield-halved"></i> Area Supervisi Sub-Departemen
                </div>
                <h3 class="text-lg font-black text-white m-0">Sub-Departemen yang Disupervisi</h3>
            </div>

            @if ($supervisedStats)
            <div class="flex items-center gap-4 text-xs">
                <div class="bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-xs border border-white/10">
                    <span class="block text-blue-200 text-[10px] uppercase font-bold">Total Project</span>
                    <strong class="text-white text-base">{{ $supervisedStats['totalSupervisedProjects'] }} Project</strong>
                </div>
                <div class="bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-xs border border-white/10">
                    <span class="block text-blue-200 text-[10px] uppercase font-bold">Total Employee</span>
                    <strong class="text-white text-base">{{ $supervisedStats['totalSupervisedEmployees'] }} Orang</strong>
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 pt-1">
            @forelse ($user->supervisedSubDepartments as $sd)
            <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/20 flex items-center gap-2 text-xs">
                <i class="fa-solid fa-folder-tree text-blue-300"></i>
                <span class="font-extrabold text-white">{{ $sd->txtSubDepartmentCode }}</span>
                <span class="text-blue-100">&bull; {{ $sd->txtSubDepartmentName }}</span>
            </div>
            @empty
            <p class="text-xs text-blue-200 m-0">Belum ada sub-departemen yang ditugaskan ke supervisor ini.</p>
            @endforelse
        </div>
    </div>
    @endif

    <!-- 3. Exposure S-Curve Chart Section -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-50 text-[#006838] text-xs font-bold border border-emerald-200 mb-1">
                    <i class="fa-solid fa-chart-area"></i> Individual Performance S-Curve
                </div>
                <h3 class="text-lg font-black text-gray-900 m-0">Kurva Exposure S-Curve Karyawan</h3>
                <p class="text-xs text-gray-500 m-0 mt-0.5">Perbandingan progres rencana kumulatif (Planned) vs realisasi nyata (Actual) dari seluruh project KPI.</p>
            </div>

            <!-- Project Selector Filter -->
            <div class="flex items-center gap-3 shrink-0">
                <label for="profileProjectFilter" class="text-xs font-bold text-gray-600 hidden sm:inline">Pilih Project:</label>
                <select id="profileProjectFilter" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-[#006838] focus:bg-white transition cursor-pointer">
                    <option value="all">Semua Project (Kumulatif {{ $totalProjects }})</option>
                    @foreach ($projects as $prj)
                    <option value="{{ $prj->intProject_ID }}">{{ $prj->txtProjectCode ?: 'PRJ' }} - {{ $prj->txtProjectName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- S-Curve Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex items-center justify-between">
                <div>
                    <span class="block text-[10px] font-bold text-indigo-700 uppercase">Target Rencana (Planned)</span>
                    <strong class="text-xl font-black text-[#6D5BD0] mt-0.5 block" id="profilePlanStat">{{ $avgPlan }}%</strong>
                    <span class="text-[11px] text-indigo-600">Bobot akumulasi target</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-[#6D5BD0] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 flex items-center justify-between">
                <div>
                    <span class="block text-[10px] font-bold text-emerald-800 uppercase">Realisasi (Actual)</span>
                    <strong class="text-xl font-black text-[#006838] mt-0.5 block" id="profileActualStat">{{ $avgActual }}%</strong>
                    <span class="text-[11px] text-emerald-700">Progres riil terverifikasi</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#006838] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>

            @php
            $gap = $avgActual - $avgPlan;
            $gapClass = $gap >= 0 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200';
            $gapStatus = $gap >= 0 ? ($gap > 0 ? 'Ahead of Schedule (+'.round($gap, 1).'%)' : 'On Track (0%)') : 'Behind Schedule ('.round($gap, 1).'%)';
            @endphp
            <div class="p-4 rounded-2xl {{ $gap >= 0 ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100' }} border flex items-center justify-between">
                <div>
                    <span class="block text-[10px] font-bold text-gray-500 uppercase">Variance / Deviasi (Gap)</span>
                    <strong class="text-xl font-black {{ $gap >= 0 ? 'text-emerald-700' : 'text-rose-600' }} mt-0.5 block" id="profileGapStat">
                        {{ $gap > 0 ? '+' : '' }}{{ round($gap, 1) }}%
                    </strong>
                    <span class="text-[11px] font-semibold {{ $gap >= 0 ? 'text-emerald-600' : 'text-rose-600' }}" id="profileGapStatus">
                        {{ $gapStatus }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $gap >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center text-lg">
                    <i class="fa-solid {{ $gap >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                </div>
            </div>
        </div>

        <!-- Canvas Chart -->
        <div class="h-80 w-full relative bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
            <canvas id="profileExposureChart"></canvas>
            <div id="profileExposureEmpty" class="hidden absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-white/90 rounded-2xl">
                <i class="fa-solid fa-chart-line text-3xl mb-2 text-gray-300"></i>
                <strong class="text-sm text-gray-700">Tidak ada data jadwal stage</strong>
                <span class="text-xs text-gray-500">Project ini belum memiliki jadwal tahapan (stage).</span>
            </div>
        </div>
    </div>

    <!-- 4. Projects Portfolio Breakdown -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-base font-black text-gray-900 m-0">Portofolio Project KPI ({{ $totalProjects }})</h3>
                <p class="text-xs text-gray-500 m-0 mt-0.5">Daftar seluruh project yang ditugaskan kepada {{ $user->txtEmployeeName }}.</p>
            </div>

            @if ($isOwnProfile || in_array(session('auth_role'), ['Head', 'Supervisor', 'Superadmin']))
            <a href="{{ route('projects.create') }}" class="px-3.5 py-1.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto no-underline">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Project</span>
            </a>
            @endif
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3">Project</th>
                        <th class="p-3">Tipe KPI</th>
                        <th class="p-3">Deliverable & Target Skala</th>
                        <th class="p-3 text-center">Bobot</th>
                        <th class="p-3 text-center">Skor</th>
                        <th class="p-3 text-center">Plan vs Actual</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($projects as $prj)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="p-3">
                            <div class="font-extrabold text-gray-900 text-sm">{{ $prj->txtProjectName }}</div>
                            <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                {{ $prj->txtProjectCode ?: 'PRJ-'.$prj->intProject_ID }} &bull; Level: <strong class="text-gray-600">{{ $prj->txtKpiLevel ?: 'Individu' }}</strong>
                            </div>
                            @if ($prj->bitHasSubProject && $prj->subProjects->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach ($prj->subProjects as $sub)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-100 text-[#006838] text-[10px] font-semibold">
                                    {{ $sub->txtSubProjectName }} ({{ $sub->floatProgress }}%)
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="p-3">
                            @php
                            $typeColor = $prj->projectType?->txtColor ?: '#006838';
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-white font-bold text-[10px] shadow-2xs" style="background-color: {{ $typeColor }}">
                                {{ $prj->projectType?->txtProjectTypeCode ?: 'IPP' }}
                            </span>
                        </td>
                        <td class="p-3 max-w-xs">
                            <div class="text-gray-800 font-medium line-clamp-1">{{ $prj->txtDeliverable ?: '-' }}</div>
                            @if ($prj->txtTargetSkalaGrade)
                            <div class="text-[10px] text-gray-400 line-clamp-1 mt-0.5">{{ Str::limit($prj->txtTargetSkalaGrade, 40) }}</div>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <span class="font-extrabold text-gray-800">{{ $prj->floatWeight ?: 0 }}%</span>
                        </td>
                        <td class="p-3 text-center">
                            @if ($prj->intScore)
                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 font-extrabold text-xs">
                                {{ $prj->intScore }} / 5
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3 text-center min-w-[140px]">
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="text-indigo-600 font-semibold">{{ $prj->floatPlan }}%</span>
                                <span class="text-[#006838] font-black">{{ $prj->floatActual }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-[#8CC63F] h-2 rounded-full transition-all" style="width: {{ min(100, max(0, $prj->floatActual)) }}%"></div>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            @php
                            $statusBadge = match($prj->txtStatus) {
                            'Completed', 'Selesai' => 'bg-green-100 text-green-800',
                            'In Progress', 'Berjalan' => 'bg-blue-100 text-blue-800',
                            'Delayed', 'Terlambat' => 'bg-rose-100 text-rose-800',
                            default => 'bg-gray-100 text-gray-700',
                            };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $statusBadge }}">
                                {{ $prj->txtStatus ?: 'Active' }}
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            <a href="{{ route('projects.show', $prj) }}" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#006838] hover:text-white text-gray-700 font-bold text-[11px] transition inline-flex items-center gap-1 no-underline">
                                <i class="fa-solid fa-eye"></i>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-400">
                            <i class="fa-solid fa-folder-open text-2xl mb-1 block"></i>
                            <span>Belum ada project yang ditugaskan ke employee ini.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Recent Daily Tasks (Activity Log) & Weekly Plans Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Recent Daily Tasks (7 cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-black text-gray-900 m-0">Log Daily Tasks Terakhir</h3>
                        <p class="text-xs text-gray-500 m-0 mt-0.5">Catatan pengerjaan tugas harian (Handsontable Log).</p>
                    </div>

                    <a href="{{ route('reports.daily-tasks') }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#006838] font-bold text-xs transition inline-flex items-center gap-1.5 no-underline">
                        <i class="fa-solid fa-table-cells"></i>
                        <span>Lihat Semua</span>
                    </a>
                </div>

                <div class="overflow-x-auto custom-scrollbar mt-3">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                            <tr>
                                <th class="p-2.5">Tanggal</th>
                                <th class="p-2.5">Project & Deskripsi</th>
                                <th class="p-2.5 text-center">Durasi</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentDailyTasks as $task)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="p-2.5 whitespace-nowrap">
                                    <div class="font-bold text-gray-800">{{ $task->dtmTaskDate ? \Carbon\Carbon::parse($task->dtmTaskDate)->format('d M Y') : '-' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $task->dtmTaskDate ? \Carbon\Carbon::parse($task->dtmTaskDate)->translatedFormat('l') : '' }}</div>
                                </td>
                                <td class="p-2.5">
                                    <div class="font-bold text-emerald-900 text-[11px]">{{ $task->project?->txtProjectName ?: 'General Task' }}</div>
                                    <div class="text-gray-700 text-xs mt-0.5 line-clamp-1">{{ $task->txtActivityDescription }}</div>
                                    @if ($task->txtDeliverableOutput)
                                    <div class="text-[10px] text-gray-400 mt-0.5 italic">Output: {{ $task->txtDeliverableOutput }}</div>
                                    @endif
                                </td>
                                <td class="p-2.5 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 font-bold text-xs">
                                        {{ $task->floatDurationHours ?: 0 }} Jam
                                    </span>
                                </td>
                                <td class="p-2.5 text-center whitespace-nowrap">
                                    @php
                                    $taskBadge = match($task->txtTaskStatus) {
                                    'Completed' => 'bg-green-100 text-green-800',
                                    'In Progress' => 'bg-blue-100 text-blue-800',
                                    'Pending' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-gray-100 text-gray-700',
                                    };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $taskBadge }}">
                                        {{ $task->txtTaskStatus ?: 'Logged' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-5 text-center text-gray-400 text-xs">
                                    <i class="fa-solid fa-list-check text-xl mb-1 block"></i>
                                    <span>Belum ada daily task yang di-input.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Total Durasi Log: <strong class="text-gray-800">{{ $totalHoursLogged }} Jam</strong></span>
                <span>Total Task: <strong class="text-emerald-700">{{ $totalDailyTasks }} Log</strong></span>
            </div>
        </div>

        <!-- Recent Weekly Plans (5 cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-black text-gray-900 m-0">Rencana Kerja Mingguan</h3>
                        <p class="text-xs text-gray-500 m-0 mt-0.5">Weekly Plan & Aktivitas Senin - Jumat.</p>
                    </div>

                    <a href="{{ route('reports.daily-plans') }}" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition inline-flex items-center gap-1.5 no-underline">
                        <i class="fa-solid fa-calendar-week"></i>
                        <span>Lihat Semua</span>
                    </a>
                </div>

                <div class="space-y-3 mt-3 overflow-y-auto max-h-96 pr-1 custom-scrollbar">
                    @forelse ($recentWeeklyPlans as $plan)
                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 hover:border-indigo-200 transition space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <strong class="text-xs text-gray-900 block">{{ $plan->txtWeekTitle ?: 'Weekly Plan #'.$plan->intWeeklyPlan_ID }}</strong>
                                <span class="text-[11px] text-gray-400">
                                    {{ $plan->dtmWeekStartDate ? \Carbon\Carbon::parse($plan->dtmWeekStartDate)->format('d M') : '' }} -
                                    {{ $plan->dtmWeekEndDate ? \Carbon\Carbon::parse($plan->dtmWeekEndDate)->format('d M Y') : '' }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                {{ $plan->txtStatus ?: 'Draft' }}
                            </span>
                        </div>

                        @if ($plan->txtTargetGoals)
                        <p class="text-xs text-gray-600 m-0 line-clamp-1 italic">
                            Goal: {{ $plan->txtTargetGoals }}
                        </p>
                        @endif

                        <div class="flex items-center justify-between pt-1 text-[11px] text-gray-500">
                            <span>Aktivitas: <strong class="text-gray-800">{{ $plan->activities->count() }} kegiatan</strong></span>
                            <span class="font-bold text-emerald-700">
                                {{ $plan->activities->where('bitIsCompleted', true)->count() }} Selesai
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-gray-400 text-xs">
                        <i class="fa-solid fa-calendar-xmark text-2xl mb-1 block"></i>
                        <span>Belum ada weekly plan yang dibuat.</span>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Total Weekly Plan: <strong class="text-gray-800">{{ $totalWeeklyPlans }}</strong></span>
                <span>Tingkat Selesai: <strong class="text-indigo-700">{{ $activityCompletionRate }}%</strong></span>
            </div>
        </div>

    </div>

    <!-- 6. Account & Contact Details Card -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <h3 class="text-base font-black text-gray-900 m-0">Informasi Kontak & Akun</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Resmi Kalbe</span>
                <strong class="text-gray-900 text-sm mt-0.5 block truncate">{{ $user->txtEmail }}</strong>
            </div>

            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nomor WhatsApp</span>
                <strong class="text-emerald-700 text-sm mt-0.5 block flex items-center gap-1.5 truncate">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i> {{ $user->txtPhone ?: '-' }}
                </strong>
            </div>

            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Terdaftar Pada</span>
                <strong class="text-gray-800 text-sm mt-0.5 block">
                    {{ $user->dtmInserted ? \Carbon\Carbon::parse($user->dtmInserted)->format('d M Y H:i') : '-' }}
                </strong>
            </div>

            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Terakhir Diperbarui</span>
                <strong class="text-gray-800 text-sm mt-0.5 block">
                    {{ $user->dtmUpdated ? \Carbon\Carbon::parse($user->dtmUpdated)->format('d M Y H:i') : '-' }}
                </strong>
            </div>
        </div>
    </div>

</div>

<!-- Payload Data for S-Curve Chart -->
<script type="application/json" id="profileExposurePayload">
    @json($exposurePayload)
</script>

<!-- Profile S-Curve Chart Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const payloadEl = document.getElementById('profileExposurePayload');
        const canvas = document.getElementById('profileExposureChart');
        const emptyState = document.getElementById('profileExposureEmpty');
        const filterSelect = document.getElementById('profileProjectFilter');
        const planStat = document.getElementById('profilePlanStat');
        const actualStat = document.getElementById('profileActualStat');
        const gapStat = document.getElementById('profileGapStat');
        const gapStatus = document.getElementById('profileGapStatus');

        if (!payloadEl || !canvas || !window.Chart) return;

        const payload = JSON.parse(payloadEl.textContent || '{}');
        const projects = Array.isArray(payload.projects) ? payload.projects : [];
        let chartInstance = null;

        const parseDate = (val) => {
            if (!val) return null;
            const d = new Date(String(val).replace(' ', 'T'));
            return Number.isNaN(d.getTime()) ? null : d.getTime();
        };

        const formatDateLabel = (timestamp) => {
            if (!timestamp) return '--';
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }).format(new Date(timestamp));
        };

        const buildProjectPoints = (project) => {
            const stages = (project.stages || []).sort((a, b) => Number(a.number) - Number(b.number));
            if (stages.length === 0) return [];

            const planTotal = stages.reduce((sum, s) => sum + Number(s.plan || 0), 0);
            const divisor = planTotal > 0 ? planTotal : 100;
            let lastDate = parseDate(project.start) || Date.now();
            let cumPlan = 0;
            let cumAct = 0;

            const points = [{
                label: 'Start (' + formatDateLabel(lastDate) + ')',
                planned: 0,
                actual: 0
            }];

            stages.forEach((st, idx) => {
                const nextDate = parseDate(st.end) || (lastDate + 86400000 * 30);
                cumPlan += Number(st.plan || 0);
                cumAct += Number(st.actual || 0);
                lastDate = nextDate;

                points.push({
                    label: (st.step || 'Stage ' + (idx + 1)) + ' (' + formatDateLabel(nextDate) + ')',
                    planned: Math.min(100, Math.round((cumPlan / divisor) * 1000) / 10),
                    actual: Math.min(100, Math.round((cumAct / divisor) * 1000) / 10)
                });
            });

            return points;
        };

        const buildCumulativePoints = () => {
            if (projects.length === 0) return [];

            // If only 1 project, just return its points
            if (projects.length === 1) {
                return buildProjectPoints(projects[0]);
            }

            let totalWeight = projects.reduce((sum, p) => sum + (Number(p.weight) || 1), 0);
            if (totalWeight <= 0) totalWeight = projects.length;

            const currentYear = new Date().getFullYear();
            const points = [];

            // Sample checkpoint periods for year S-Curve
            const checkpoints = [{
                    label: 'Q1 (Mar ' + currentYear + ')',
                    planRatio: 0.25
                },
                {
                    label: 'Q2 (Jun ' + currentYear + ')',
                    planRatio: 0.50
                },
                {
                    label: 'Q3 (Sep ' + currentYear + ')',
                    planRatio: 0.75
                },
                {
                    label: 'Q4 (Dec ' + currentYear + ')',
                    planRatio: 1.00
                }
            ];

            let avgActualOverall = 0;
            projects.forEach(p => {
                avgActualOverall += (Number(p.actual || 0) * ((Number(p.weight) || 1) / totalWeight));
            });

            points.push({
                label: 'Kickoff (' + currentYear + ')',
                planned: 0,
                actual: 0
            });

            checkpoints.forEach((cp, idx) => {
                const targetPlan = Math.round(cp.planRatio * 100);
                let targetActual = 0;
                if (idx === 0) targetActual = Math.min(targetPlan, Math.round(avgActualOverall * 0.35));
                else if (idx === 1) targetActual = Math.min(targetPlan, Math.round(avgActualOverall * 0.70));
                else if (idx === 2) targetActual = Math.min(targetPlan, Math.round(avgActualOverall));
                else targetActual = Math.min(100, Math.round(avgActualOverall));

                points.push({
                    label: cp.label,
                    planned: targetPlan,
                    actual: targetActual
                });
            });

            return points;
        };

        const updateChart = (selectedVal) => {
            let points = [];
            let finalPlan = 0;
            let finalActual = 0;

            if (selectedVal === 'all') {
                points = buildCumulativePoints();
                if (projects.length > 0) {
                    finalPlan = 100;
                    let totalW = projects.reduce((s, p) => s + (Number(p.weight) || 1), 0);
                    if (totalW <= 0) totalW = projects.length;
                    finalActual = Math.round(projects.reduce((s, p) => s + (Number(p.actual || 0) * ((Number(p.weight) || 1) / totalW)), 0) * 10) / 10;
                }
            } else {
                const prj = projects.find(p => String(p.id) === String(selectedVal));
                if (prj) {
                    points = buildProjectPoints(prj);
                    finalPlan = Number(prj.planned || 100);
                    finalActual = Number(prj.actual || 0);
                }
            }

            if (points.length === 0) {
                canvas.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            canvas.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Update top stats
            if (planStat) planStat.textContent = finalPlan + '%';
            if (actualStat) actualStat.textContent = finalActual + '%';
            const gap = Math.round((finalActual - finalPlan) * 10) / 10;
            if (gapStat) gapStat.textContent = (gap > 0 ? '+' : '') + gap + '%';
            if (gapStatus) {
                gapStatus.textContent = gap >= 0 ? (gap > 0 ? 'Ahead of Schedule (+' + gap + '%)' : 'On Track (0%)') : 'Behind Schedule (' + gap + '%)';
                gapStatus.className = 'text-[11px] font-semibold ' + (gap >= 0 ? 'text-emerald-600' : 'text-rose-600');
            }

            const labels = points.map(pt => pt.label);
            const plannedData = points.map(pt => pt.planned);
            const actualData = points.map(pt => pt.actual);

            if (chartInstance) {
                chartInstance.destroy();
            }

            const ctx = canvas.getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Planned Cumulative (%)',
                            data: plannedData,
                            borderColor: '#6D5BD0',
                            backgroundColor: 'rgba(109, 91, 208, 0.08)',
                            borderWidth: 2.5,
                            borderDash: [5, 5],
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#6D5BD0',
                            fill: true
                        },
                        {
                            label: 'Actual Cumulative (%)',
                            data: actualData,
                            borderColor: '#006838',
                            backgroundColor: 'rgba(0, 104, 56, 0.12)',
                            borderWidth: 3,
                            tension: 0.35,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#8CC63F',
                            pointBorderColor: '#006838',
                            pointBorderWidth: 2,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: 'bold'
                                },
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: {
                                family: 'Inter',
                                size: 12,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 11
                            },
                            padding: 10,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                callback: function(val) {
                                    return val + '%';
                                },
                                font: {
                                    family: 'Inter',
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(221, 229, 221, 0.6)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 10
                                },
                                maxRotation: 25
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        };

        // Initial render
        updateChart(filterSelect ? filterSelect.value : 'all');

        // Filter change handler
        if (filterSelect) {
            filterSelect.addEventListener('change', (e) => {
                updateChart(e.target.value);
            });
        }
    });
</script>
@endsection