@extends('layouts.app', [
'title' => 'Monthly Report & Progress Dashboard - KMI Activity Plan',
'pageTitle' => 'LAPORAN PROGRESS & PERKEMBANGAN BULANAN',
'pageSubtitle' => '<span>Monitoring Perkembangan Project & Aktivitas Bulanan</span> &bull; <span>' . $monthName . '</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Laporan Perkembangan & Progress</h2>
            <p class="text-xs text-gray-500 m-0">Visualisasi data perkembangan project, perbandingan plan vs actual, dan tren aktivitas bulanan.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs border border-gray-300 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-print text-[#006838]"></i>
                <span>Cetak / PDF</span>
            </button>
            <a href="{{ route('exposure.index') }}" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline">
                <i class="fa-solid fa-chart-line"></i>
                <span>Lihat S-Curve Exposure</span>
            </a>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs">
        <form method="GET" action="{{ route('reports.monthly-report') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Periode Bulan</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none font-semibold">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sub Department</label>
                <select name="subdept" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none font-medium">
                    <option value="">Semua Sub Dept</option>
                    @foreach ($subDepartments as $sd)
                    <option value="{{ $sd->intSubDepartment_ID }}" {{ $selectedSubDept == $sd->intSubDepartment_ID ? 'selected' : '' }}>
                        {{ $sd->txtSubDepartmentCode }} - {{ $sd->txtSubDepartmentName }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Employee PIC</label>
                <select name="employee" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none font-medium">
                    <option value="">Semua Employee</option>
                    @foreach ($allEmployees as $emp)
                    <option value="{{ $emp->intUser_ID }}" {{ $selectedEmployee == $emp->intUser_ID ? 'selected' : '' }}>
                        {{ $emp->txtEmployeeName }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tipe Project</label>
                <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none font-medium">
                    <option value="">Semua Tipe Project</option>
                    @foreach ($projectTypes as $pt)
                    <option value="{{ $pt->intProjectType_ID }}" {{ $selectedType == $pt->intProjectType_ID ? 'selected' : '' }}>
                        {{ $pt->txtProjectTypeCode }} - {{ $pt->txtProjectTypeName }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Project & Bobot</span>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ $totalProjects }} <span class="text-xs font-semibold text-gray-400">Project</span></div>
                <span class="text-xs text-emerald-700 font-bold mt-0.5 block">Total Bobot: {{ $totalWeight }}%</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#006838] flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Rata-rata Progress Actual</span>
                <div class="text-2xl font-black text-[#006838] mt-1">{{ $avgActual }}%</div>
                <div class="w-24 h-1.5 rounded-full bg-gray-100 mt-1.5 overflow-hidden">
                    <div class="h-full bg-[#8CC63F] rounded-full" style="width: {{ min(100, $avgActual) }}%"></div>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-lime-50 text-[#006838] flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Project Selesai 100%</span>
                <div class="text-2xl font-black text-blue-600 mt-1">{{ $completedProjectsCount }} <span class="text-xs font-semibold text-gray-400">/ {{ $totalProjects }}</span></div>
                <span class="text-xs text-blue-700 font-bold mt-0.5 block">Tingkat Capaian: {{ $completionRate }}%</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Logbook Task (Bulan Ini)</span>
                <div class="text-2xl font-black text-amber-500 mt-1">{{ array_sum($chartTrendCounts) }} <span class="text-xs font-semibold text-gray-400">Tasks</span></div>
                <span class="text-xs text-amber-700 font-bold mt-0.5 block">Total Jam: {{ array_sum($chartTrendHours) }} Jam</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>
    </div>

    <!-- MAIN PROGRESS CHARTS SECTION (6-Column Grid Layout for Larger Visuals) -->
    <!-- MAIN PROGRESS CHARTS SECTION (6-Column Grid Layout for Balanced Executive View) -->
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-6">

        <!-- Chart 1: Perbandingan Plan vs Actual Progress Per Project (Span 4 of 6 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-[#006838]"></i>
                        <span>Perbandingan Progress Plan vs Actual Per Project</span>
                    </h3>
                    <p class="text-xs text-gray-500 m-0">Menunjukkan pencapaian persentase actual terhadap target exposure.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-[#006838] font-bold text-[11px] border border-emerald-100">
                    {{ count($chartProjectLabels) }} Projects
                </span>
            </div>

            <div style="position: relative; height: 380px; width: 100%;">
                <canvas id="projectProgressChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Year To Date (YTD) - Project vs Ad Hoc Dominance (Span 2 of 6 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-scale-balanced text-[#0D9488]"></i>
                        <span>Year To Date (YTD)</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-bold text-[10px] border border-teal-200">
                        {{ $ytdData['periodLabel'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 m-0">Komparasi beban kerja Project (IPP, MAR, dll) vs Ad Hoc.</p>
            </div>

            <!-- Dominance Callout Banner -->
            <div class="p-3 rounded-2xl border {{ $ytdData['dominantBadgeClass'] }} flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-xl bg-white/90 shadow-xs flex items-center justify-center shrink-0 mt-0.5">
                    <i class="{{ $ytdData['dominantIcon'] }} text-xs"></i>
                </div>
                <div class="text-xs">
                    <div class="font-extrabold tracking-tight flex items-center gap-1.5">
                        <span>{{ $ytdData['dominantLabel'] }}</span>
                        @if($ytdData['dominantType'] === 'Ad Hoc')
                        <span class="px-1.5 py-0.2 rounded bg-teal-600 text-white text-[9px] font-black uppercase tracking-wider">Dominan</span>
                        @elseif($ytdData['dominantType'] === 'Planned')
                        <span class="px-1.5 py-0.2 rounded bg-[#006838] text-white text-[9px] font-black uppercase tracking-wider">Dominan</span>
                        @endif
                    </div>
                    <p class="text-[11px] opacity-90 mt-0.5 leading-snug m-0">
                        {{ $ytdData['dominantMessage'] }}
                    </p>
                </div>
            </div>

            <!-- Comparative Progress Ratio Bar -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-[11px] font-bold">
                    <span class="text-[#006838] flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#006838]"></span>
                        Project: {{ $ytdData['plannedPct'] }}%
                    </span>
                    <span class="text-teal-700 flex items-center gap-1">
                        Ad Hoc: {{ $ytdData['adHocPct'] }}%
                        <span class="w-2 h-2 rounded-full bg-[#0D9488]"></span>
                    </span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 flex overflow-hidden">
                    <div class="h-full bg-[#006838] transition-all duration-500" style="width: {{ $ytdData['plannedPct'] }}%" title="Project Terencana: {{ $ytdData['plannedPct'] }}% ({{ $ytdData['plannedHours'] }} Jam)"></div>
                    <div class="h-full bg-[#0D9488] transition-all duration-500" style="width: {{ $ytdData['adHocPct'] }}%" title="Ad Hoc: {{ $ytdData['adHocPct'] }}% ({{ $ytdData['adHocHours'] }} Jam)"></div>
                </div>
            </div>

            <!-- Doughnut Chart Canvas -->
            <div style="position: relative; height: 160px; width: 100%; display: flex; align-items: center; justify-content: center;">
                <canvas id="ytdDoughnutChart"></canvas>
            </div>

            <!-- Category Breakdown Pills -->
            <div class="space-y-1.5 pt-2 border-t border-gray-100 text-xs">
                @foreach($ytdData['categories'] as $catKey => $cat)
                <div class="flex items-center justify-between text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $cat['color'] }}"></span>
                        <span class="font-bold text-gray-700">{{ $cat['code'] }}</span>
                        <span class="text-gray-400 text-[10px]">({{ $cat['projects'] }} proj)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-[10px]">{{ $cat['tasks'] }} task</span>
                        <span class="font-extrabold text-gray-900 w-16 text-right">{{ $cat['hours'] }} Jam</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 min-w-[36px] text-right">{{ $cat['hours_pct'] }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Chart 3: Distribusi Status & Kesehatan Project (Span 2 of 6 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                        <span>Distribusi Status Project</span>
                    </h3>
                </div>
                <p class="text-xs text-gray-500 m-0">Kategori kesehatan progress capaian project.</p>
            </div>

            <div style="position: relative; height: 200px; width: 100%; display: flex; align-items: center; justify-content: center;">
                <canvas id="projectStatusChart"></canvas>
            </div>

            <!-- Legend Pills -->
            <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-gray-100 font-semibold">
                <div class="flex items-center gap-1.5 text-emerald-800">
                    <span class="w-2.5 h-2.5 rounded-md bg-[#006838] shrink-0"></span>
                    <span>Selesai: <strong>{{ $statusCounts['completed'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5 text-emerald-700">
                    <span class="w-2.5 h-2.5 rounded-md bg-[#8CC63F] shrink-0"></span>
                    <span>On Track: <strong>{{ $statusCounts['on_track'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5 text-amber-700">
                    <span class="w-2.5 h-2.5 rounded-md bg-[#FBBF24] shrink-0"></span>
                    <span>In Progress: <strong>{{ $statusCounts['in_progress'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5 text-rose-700">
                    <span class="w-2.5 h-2.5 rounded-md bg-[#F87171] shrink-0"></span>
                    <span>Perhatian: <strong>{{ $statusCounts['needs_action'] }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Chart 4: Tren Aktivitas Daily Task Sepanjang Bulan (Span 2 of 6 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-trend-up text-blue-600"></i>
                        <span>Tren Aktivitas Daily Task</span>
                    </h3>
                    <p class="text-xs text-gray-500 m-0">Task harian bulan {{ $monthName }}.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                    {{ count($chartTrendLabels) }} Hari
                </span>
            </div>

            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 5: Progress & Beban Kerja per Sub-Department (Span 2 of 6 Cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-sitemap text-purple-600"></i>
                    <span>Kinerja per Sub-Dept</span>
                </h3>
                <p class="text-xs text-gray-500 m-0">Rata-rata actual progress & total bobot.</p>
            </div>

            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="subDeptProgressChart"></canvas>
            </div>
        </div>

    </div>

    <!-- STREAMLINED PROJECT PROGRESS TRACKER TABLE -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-[#006838]"></i>
                    <span>Tabel Ringkasan Perkembangan Project</span>
                </h3>
                <p class="text-xs text-gray-500 m-0">Detail progress exposure dan capaian masing-masing project.</p>
            </div>
            <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $projects->count() }} Project Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No.</th>
                        <th class="py-3 px-4 min-w-[240px]">Nama Project</th>
                        <th class="py-3 px-3 text-center w-28">Sub Dept</th>
                        <th class="py-3 px-3 min-w-[140px]">PIC Employee</th>
                        <th class="py-3 px-3 text-center w-24">Bobot</th>
                        <th class="py-3 px-4 min-w-[200px]">Progress Actual (%)</th>
                        <th class="py-3 px-3 text-center w-28">Status</th>
                        <th class="py-3 px-3 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($projects as $idx => $p)
                    <tr class="hover:bg-emerald-50/30 transition">
                        <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4">
                            <a href="{{ route('projects.show', $p) }}" class="font-extrabold text-gray-900 hover:text-[#006838] transition no-underline block text-xs">
                                {{ $p->txtProjectName }}
                            </a>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-400 font-mono">{{ $p->txtProjectCode ?? 'PRJ-'.$p->intProject_ID }}</span>
                                @if ($p->bitHasSubProject)
                                <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-800 text-[9px] font-bold">
                                    {{ $p->subProjects->count() }} Sub-Projects
                                </span>
                                @endif
                                <span class="px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 text-[9px] font-semibold">
                                    {{ $p->projectType?->txtProjectTypeCode ?? 'Type' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-gray-700">
                            <span class="px-2 py-0.5 rounded-lg bg-gray-100 text-gray-800 text-[11px]">
                                {{ $p->subDepartment?->txtSubDepartmentCode ?? 'MDP' }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-bold text-gray-800 block text-xs">{{ $p->user?->txtEmployeeName ?? 'Unassigned' }}</span>
                            <span class="text-[10px] text-gray-400">{{ $p->txtKpiLevel }}</span>
                        </td>
                        <td class="py-3 px-3 text-center font-black text-gray-900 text-xs">
                            {{ $p->floatWeight }}%
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-between text-[11px] font-extrabold mb-1">
                                <span class="text-[#006838]">{{ $p->floatActual }}%</span>
                                <span class="text-gray-400 font-normal">Target: {{ $p->floatPlan }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $p->floatActual >= 100 ? 'bg-[#006838]' : ($p->floatActual >= 75 ? 'bg-[#8CC63F]' : ($p->floatActual >= 40 ? 'bg-amber-400' : 'bg-rose-400')) }}"
                                    style="width: {{ min(100, $p->floatActual) }}%"></div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            @php
                            $act = (float) $p->floatActual;
                            if ($act >= 100 || strtolower($p->txtStatus) === 'completed') {
                            $badgeClass = 'bg-emerald-100 text-[#006838] border border-emerald-200';
                            $badgeText = 'Selesai 100%';
                            } elseif ($act >= 75) {
                            $badgeClass = 'bg-lime-100 text-lime-800 border border-lime-200';
                            $badgeText = 'On Track';
                            } elseif ($act >= 40) {
                            $badgeClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                            $badgeText = 'In Progress';
                            } else {
                            $badgeClass = 'bg-rose-100 text-rose-800 border border-rose-200';
                            $badgeText = 'Perhatian';
                            }
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold inline-block {{ $badgeClass }}">
                                {{ $badgeText }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <a href="{{ route('projects.show', $p) }}" class="p-1.5 rounded-lg text-[#006838] hover:bg-emerald-100 transition inline-flex items-center justify-center">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-400">Tidak ada data project yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-extrabold text-xs text-gray-900">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-right uppercase tracking-wider text-gray-500">Rata-rata / Total</td>
                        <td class="py-3 px-3 text-center font-black text-[#006838]">{{ $totalWeight }}%</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-between text-[11px] font-black text-[#006838]">
                                <span>Rata-rata: {{ $avgActual }}%</span>
                            </div>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Employee Performance Cards -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0">Ringkasan Kinerja & Beban Kerja Per Employee</h3>
                <p class="text-xs text-gray-500 m-0">Pencapaian progress individual dan kontribusi bobot kerja.</p>
            </div>
            <span class="text-xs font-bold text-gray-400">{{ count($employees) }} Employee Terdaftar</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($employees as $emp)
            <div class="p-4 rounded-2xl bg-gray-50/70 border border-gray-200/80 space-y-3 hover:border-emerald-300 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-gray-900 text-sm m-0">{{ $emp['name'] }}</h4>
                        <p class="text-[11px] text-gray-500 m-0">{{ $emp['subDept'] }} &bull; {{ $emp['position'] }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-[#006838] font-bold text-xs">
                        {{ $emp['totalProjects'] }} Proj
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center text-xs pt-1">
                    <div class="p-2 rounded-xl bg-white border border-gray-100">
                        <span class="block text-[10px] text-gray-400 uppercase font-bold">Total Bobot</span>
                        <strong class="text-gray-800 text-sm">{{ $emp['totalWeight'] }}%</strong>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-gray-100">
                        <span class="block text-[10px] text-gray-400 uppercase font-bold">Avg Progress</span>
                        <strong class="text-[#006838] text-sm">{{ $emp['avgActual'] }}%</strong>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                        <span class="text-gray-500">Progress Capaian</span>
                        <span class="text-[#006838]">{{ $emp['avgActual'] }}%</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full bg-[#8CC63F] rounded-full" style="width: {{ min(100, $emp['avgActual']) }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- JSON Chart Payload (Clean, standard, error-free data transfer from Laravel to JS) -->
    <script id="monthlyChartPayload" type="application/json">
        @json($chartPayload)
    </script>

</div>

@push('scripts')
<script>
    (function() {
        function setupCharts() {
            if (typeof Chart === 'undefined') {
                console.warn('[MonthlyReport] Chart.js not yet defined, retrying in 150ms...');
                setTimeout(setupCharts, 150);
                return;
            }

            const dataScript = document.getElementById('monthlyChartPayload');
            if (!dataScript) {
                console.error('[MonthlyReport] Element #monthlyChartPayload not found!');
                return;
            }

            let payload = {};
            try {
                payload = JSON.parse(dataScript.textContent);
            } catch (err) {
                console.error('[MonthlyReport] JSON parse error:', err);
                return;
            }

            // 1. Chart 1: Plan vs Actual Progress Per Project (Bar Chart)
            try {
                const elProject = document.getElementById('projectProgressChart');
                if (elProject) {
                    new Chart(elProject, {
                        type: 'bar',
                        data: {
                            labels: payload.projectLabels || [],
                            datasets: [{
                                    label: 'Plan Target (%)',
                                    data: payload.projectPlans || [],
                                    backgroundColor: 'rgba(209, 213, 219, 0.7)',
                                    borderColor: 'rgb(156, 163, 175)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Actual Progress (%)',
                                    data: payload.projectActuals || [],
                                    backgroundColor: 'rgba(0, 104, 56, 0.85)',
                                    borderColor: 'rgb(0, 104, 56)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 11,
                                            weight: '600'
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.dataset.label + ': ' + ctx.raw + '%';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(val) {
                                            return val + '%';
                                        },
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        },
                                        maxRotation: 45,
                                        minRotation: 0
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('[MonthlyReport] Error rendering Project Progress Chart:', e);
            }

            // 1b. Chart YTD: Year to Date Project vs Ad Hoc Dominance (Doughnut Chart)
            try {
                const elYtd = document.getElementById('ytdDoughnutChart');
                if (elYtd && payload.ytd) {
                    const ytd = payload.ytd;
                    const hasHours = Number(ytd.totalHours || 0) > 0;
                    const chartData = hasHours ? (ytd.chartHours || []) : (ytd.chartProjects || []);
                    const unitLabel = hasHours ? 'Jam' : 'Project';

                    new Chart(elYtd, {
                        type: 'doughnut',
                        data: {
                            labels: ytd.chartLabels || [],
                            datasets: [{
                                data: chartData,
                                backgroundColor: ytd.chartColors || [
                                    '#0D9488', // Ad Hoc
                                    '#006838', // IPP
                                    '#7C3AED', // MAR
                                    '#2563EB', // IDP
                                    '#F59E0B' // Routine
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const val = ctx.raw;
                                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                            return ' ' + ctx.label + ': ' + val + ' ' + unitLabel + ' (' + pct + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('[MonthlyReport] Error rendering YTD Chart:', e);
            }

            // 2. Chart 2: Status & Health Breakdown (Doughnut Chart)
            try {
                const elStatus = document.getElementById('projectStatusChart');
                if (elStatus) {
                    const sc = payload.statusCounts || {
                        completed: 0,
                        on_track: 0,
                        in_progress: 0,
                        needs_action: 0
                    };
                    new Chart(elStatus, {
                        type: 'doughnut',
                        data: {
                            labels: ['Selesai (100%)', 'On Track (≥75%)', 'In Progress (40-74%)', 'Perlu Perhatian (<40%)'],
                            datasets: [{
                                data: [
                                    Number(sc.completed || 0),
                                    Number(sc.on_track || 0),
                                    Number(sc.in_progress || 0),
                                    Number(sc.needs_action || 0)
                                ],
                                backgroundColor: [
                                    '#006838', // Kalbe Dark Green
                                    '#8CC63F', // Kalbe Light Green
                                    '#FBBF24', // Amber
                                    '#F87171' // Red
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const count = ctx.raw;
                                            const pct = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                                            return ' ' + ctx.label + ': ' + count + ' Project (' + pct + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('[MonthlyReport] Error rendering Status Chart:', e);
            }

            // 3. Chart 3: Daily Task Activity & Momentum Trend (Line Chart)
            try {
                const elTrend = document.getElementById('dailyTrendChart');
                if (elTrend) {
                    new Chart(elTrend, {
                        type: 'line',
                        data: {
                            labels: payload.trendLabels || [],
                            datasets: [{
                                    label: 'Task Selesai / Terinput',
                                    data: payload.trendCounts || [],
                                    borderColor: '#2563EB',
                                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    pointBackgroundColor: '#2563EB',
                                    borderWidth: 2,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Durasi Pengerjaan (Jam)',
                                    data: payload.trendHours || [],
                                    borderColor: '#059669',
                                    backgroundColor: 'transparent',
                                    borderDash: [4, 4],
                                    pointRadius: 2,
                                    borderWidth: 1.5,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 11,
                                            weight: '600'
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Tanggal pada Bulan Ini',
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Jumlah Task',
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Total Jam',
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('[MonthlyReport] Error rendering Daily Trend Chart:', e);
            }

            // 4. Chart 4: Sub-Department Performance & Bobot (Bar Chart)
            try {
                const elSubDept = document.getElementById('subDeptProgressChart');
                if (elSubDept) {
                    new Chart(elSubDept, {
                        type: 'bar',
                        data: {
                            labels: payload.subDeptLabels || [],
                            datasets: [{
                                    label: 'Avg Progress (%)',
                                    data: payload.subDeptAvgProgress || [],
                                    backgroundColor: 'rgba(140, 198, 63, 0.85)',
                                    borderColor: '#8CC63F',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Total Bobot (%)',
                                    data: payload.subDeptTotalWeight || [],
                                    backgroundColor: 'rgba(124, 58, 237, 0.75)',
                                    borderColor: '#7C3AED',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 10,
                                            weight: '600'
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.dataset.label + ': ' + ctx.raw + '%';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(val) {
                                            return val + '%';
                                        },
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('[MonthlyReport] Error rendering SubDept Chart:', e);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupCharts);
        } else {
            setupCharts();
        }
    })();
</script>
@endpush
@endsection