@extends('layouts.app', [
'title' => 'Exposure S-Curve - KMI Activity Plan',
'pageTitle' => 'EXPOSURE S-CURVE',
'pageSubtitle' => '<span>Planned vs Actual Cumulative Progress</span>',
])

@section('content')
<div class="space-y-6" data-exposure-page>

    <!-- Header Summary & KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Planned Cumulative</span>
                    <h3 class="text-2xl font-black text-[#6D5BD0] mt-1 m-0" id="exposureKpiPlan">--</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">Target Rencana</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-[#6D5BD0] flex items-center justify-center text-xl">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Actual Cumulative</span>
                    <h3 class="text-2xl font-black text-[#8CC63F] mt-1 m-0" id="exposureKpiActual">--</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">Progress Realisasi</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#8CC63F] flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Gap (Plan - Act)</span>
                    <h3 class="text-2xl font-black text-gray-800 mt-1 m-0" id="exposureKpiGap">--</h3>
                    <p class="text-[11px] font-bold text-gray-400 m-0 mt-0.5" id="exposureKpiGapNote">Status</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-arrows-split-up-and-left"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sumber Data</span>
                    <h3 class="text-2xl font-black text-[#006838] mt-1 m-0" id="exposureKpiSources">{{ $summary['totalProjects'] }}</h3>
                    <p class="text-[11px] text-gray-400 font-medium m-0 mt-0.5">{{ $summary['totalStages'] }} active stages</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#EBF5E9] text-[#006838] flex items-center justify-center text-xl">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Mode Toolbar -->
    <div class="bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <!-- Mode Tabs -->
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 pb-3" role="tablist">
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-[#006838] text-white shadow-xs" type="button" data-exposure-mode="main">
                <i class="fa-solid fa-chart-area mr-1.5"></i> S-Curve Utama (MDP)
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200" type="button" data-exposure-mode="project">
                <i class="fa-solid fa-briefcase mr-1.5"></i> Per Project
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200" type="button" data-exposure-mode="employee">
                <i class="fa-solid fa-user mr-1.5"></i> Per Employee
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200" type="button" data-exposure-mode="type">
                <i class="fa-solid fa-layer-group mr-1.5"></i> Per Tipe Project
            </button>
        </div>

        <!-- Filter Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div data-exposure-filter-field="project" class="hidden">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1" for="exposureProjectFilter">Pilih Project</label>
                <select class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none" id="exposureProjectFilter">
                    <option value="">Pilih Project</option>
                    @foreach ($projects as $project)
                    <option value="{{ $project->intProject_ID }}">{{ $project->txtProjectName }} ({{ $project->projectType?->txtProjectTypeCode }})</option>
                    @endforeach
                </select>
            </div>

            <div data-exposure-filter-field="employee" class="hidden">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1" for="exposureEmployeeFilter">Pilih Employee</label>
                <select class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none" id="exposureEmployeeFilter">
                    <option value="">Pilih Employee</option>
                    @foreach ($employees as $emp)
                    <option value="{{ $emp->intUser_ID }}">{{ $emp->txtEmployeeName }} ({{ $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})</option>
                    @endforeach
                </select>
            </div>

            <div data-exposure-filter-field="type" class="hidden">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1" for="exposureTypeFilter">Tipe Project</label>
                <select class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none" id="exposureTypeFilter">
                    <option value="">Pilih Tipe</option>
                    @foreach ($projectTypes as $type)
                    <option value="{{ strtolower($type->txtProjectTypeCode) }}">{{ $type->txtProjectTypeCode }} - {{ $type->txtProjectTypeName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Toggles & Reset -->
            <div class="flex items-center gap-4 py-2">
                <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                    <input id="exposureToggleActual" type="checkbox" checked class="rounded text-[#8CC63F] focus:ring-[#8CC63F]">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#8CC63F]"></span>
                    Actual
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                    <input id="exposureTogglePlan" type="checkbox" checked class="rounded text-[#6D5BD0] focus:ring-[#6D5BD0]">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#6D5BD0]"></span>
                    Planned
                </label>
                <button class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition cursor-pointer" id="exposureResetFilter" type="button">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- S-Curve Chart Panel & Type Contribution Side Card -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left 8 cols: Line Chart Canvas & Timeline Scrubber -->
        <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-gray-900 m-0" id="exposureChartTitle">S Curve Utama (MDP)</h3>
                    <p class="text-xs text-gray-500 m-0" id="exposureChartSubtitle">Akumulasi planned vs actual cumulative progress.</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#8CC63F] inline-block"></span> Actual</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#6D5BD0] inline-block"></span> Planned</span>
                </div>
            </div>

            <!-- Chart Canvas Container -->
            <div class="h-80 w-full relative">
                <canvas id="exposureCurveChart"></canvas>
                <div class="hidden absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-white/80" id="exposureEmptyState">
                    <i class="fa-solid fa-chart-simple text-3xl mb-2"></i>
                    <strong class="text-sm text-gray-700">Tidak ada data stage</strong>
                    <span class="text-xs text-gray-500">Project yang dipilih belum memiliki jadwal stage.</span>
                </div>
            </div>

            <!-- Timeline Scrubber Range Slider -->
            <div class="pt-4 border-t border-gray-100 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-gray-700" id="exposureTimelineLabel">Timeline Slider</span>
                    <output class="font-mono font-bold text-[#006838] bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200" id="exposureActivityOutput">--</output>
                </div>
                <input id="exposureActivityScrubber" type="range" min="1" max="1" value="1" class="w-full accent-[#006838] cursor-pointer">
            </div>

            <!-- Timeline Scrubber Point Details -->
            <div class="grid grid-cols-4 gap-2 pt-2 border-t border-gray-100 text-center">
                <div class="p-2 rounded-xl bg-gray-50">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase" id="exposurePointDateLabel">Periode</span>
                    <strong class="text-xs text-gray-800" id="exposurePointActivity">--</strong>
                </div>
                <div class="p-2 rounded-xl bg-indigo-50">
                    <span class="block text-[10px] font-bold text-indigo-400 uppercase">Planned</span>
                    <strong class="text-xs text-[#6D5BD0]" id="exposurePointPlan">--</strong>
                </div>
                <div class="p-2 rounded-xl bg-emerald-50">
                    <span class="block text-[10px] font-bold text-emerald-400 uppercase">Actual</span>
                    <strong class="text-xs text-[#006838]" id="exposurePointActual">--</strong>
                </div>
                <div class="p-2 rounded-xl bg-amber-50">
                    <span class="block text-[10px] font-bold text-amber-400 uppercase">Gap</span>
                    <strong class="text-xs text-amber-700" id="exposurePointGap">--</strong>
                </div>
            </div>
        </div>

        <!-- Right 4 cols: Project Type Contribution List -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-black text-gray-900 m-0">Kontribusi Tipe KPI</h3>
                <p class="text-xs text-gray-500 m-0" id="exposureContributionSubtitle">Distribusi bobot & realisasi.</p>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-96" id="exposureContributionList">
                <!-- Dynamically populated via exposure.js -->
            </div>
        </div>
    </div>

    <!-- Cumulative Table & Source Projects Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Cumulative Table -->
        <div class="lg:col-span-6 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Tabel Kumulatif Progres</h3>
            <div class="overflow-x-auto max-h-80 custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase sticky top-0">
                        <tr>
                            <th class="p-2.5">Periode / Waktu</th>
                            <th class="p-2.5 text-center text-indigo-600">Planned (%)</th>
                            <th class="p-2.5 text-center text-[#006838]">Actual (%)</th>
                            <th class="p-2.5 text-center">Gap</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="exposureCurveTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Source Projects Table -->
        <div class="lg:col-span-6 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Daftar Project Sumber Kurva</h3>
            <div class="overflow-x-auto max-h-80 custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase sticky top-0">
                        <tr>
                            <th class="p-2.5">Project</th>
                            <th class="p-2.5">Tipe</th>
                            <th class="p-2.5 text-center">Stages</th>
                            <th class="p-2.5 text-center">Actual (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="exposureSourceTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script type="application/json" id="exposureCurvePayload">
    @json($exposurePayload)
</script>
@endsection