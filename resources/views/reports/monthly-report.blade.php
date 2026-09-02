@extends('layouts.app', [
'title' => 'Monthly Report Dashboard - KMI Activity Plan',
'pageTitle' => 'MONTHLY REPORT & KPI DASHBOARD',
'pageSubtitle' => '<span>Yearly KPI Matrix</span> &bull; <span>Department & Employee Summary</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Matriks Laporan Bulanan KPI</h2>
            <p class="text-xs text-gray-500 m-0">Ringkasan pencapaian KPI tahunan departemen & kinerja employee.</p>
        </div>

        <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / PDF</span>
        </button>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs">
        <form method="GET" action="{{ route('reports.monthly-report') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Periode Bulan</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sub Department</label>
                <select name="subdept" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
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
                <select name="employee" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
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
                <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <option value="">Semua Tipe</option>
                    @foreach ($projectTypes as $pt)
                    <option value="{{ $pt->intProjectType_ID }}" {{ $selectedType == $pt->intProjectType_ID ? 'selected' : '' }}>
                        {{ $pt->txtProjectTypeCode }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Bobot (Weight)</span>
            <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalWeight }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Score Terkumpul</span>
            <div class="text-2xl font-black text-amber-500 mt-0.5">{{ $totalScore }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Score</span>
            <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $avgScore }} <span class="text-xs text-gray-400">/ 5</span></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Avg Exposure Actual</span>
            <div class="text-2xl font-black text-[#8CC63F] mt-0.5">{{ $avgActual }}%</div>
        </div>
    </div>

    <!-- KPI Matrix Table matching user's reference image -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                <i class="fa-solid fa-table-cells text-[#006838]"></i>
                <span>Matriks Objective KPI, Bobot, Skala Grade & Achievement</span>
            </h3>
            <span class="text-xs font-bold text-gray-400">MDP Department 2026</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-3 w-10 text-center border-r border-gray-200">No.</th>
                        <th class="p-3 w-28 text-center bg-amber-100/60 text-amber-900 border-r border-gray-200">KPI Level</th>
                        <th class="p-3 border-r border-gray-200 min-w-[220px]">Objective Name</th>
                        <th class="p-3 text-center border-r border-gray-200 w-28">Objective Weight</th>
                        <th class="p-3 border-r border-gray-200 min-w-[160px]">Deliverable</th>
                        <th class="p-3 border-r border-gray-200 min-w-[180px]">Target Skala Grade</th>
                        <th class="p-3 text-center bg-blue-100/60 text-blue-900 border-r border-gray-200 w-20">Score</th>
                        <th class="p-3 bg-sky-100/60 text-sky-900 min-w-[140px]">Achievement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($projects as $idx => $p)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-500 border-r border-gray-200">{{ $idx + 1 }}</td>
                        <td class="p-3 text-center font-bold text-gray-800 border-r border-gray-200 bg-amber-50/30">
                            {{ $p->txtKpiLevel }}
                        </td>
                        <td class="p-3 font-extrabold text-gray-900 border-r border-gray-200 leading-tight">
                            <a href="{{ route('projects.show', $p) }}" class="text-[#006838] hover:underline no-underline">
                                {{ $p->txtProjectName }}
                            </a>
                            @if ($p->bitHasSubProject)
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($p->subProjects as $sub)
                                <span class="px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[9px] font-bold">
                                    {{ $sub->txtSubProjectName }} ({{ $sub->floatWeight }}%)
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="p-3 text-center font-black text-gray-900 border-r border-gray-200 text-sm">
                            {{ $p->floatWeight }}
                        </td>
                        <td class="p-3 text-gray-700 border-r border-gray-200 font-medium">
                            {{ $p->txtDeliverable ?: '-' }}
                        </td>
                        <td class="p-3 text-gray-700 font-mono text-[11px] border-r border-gray-200 whitespace-pre-line leading-relaxed">
                            {{ $p->txtTargetSkalaGrade ?: '-' }}
                        </td>
                        <td class="p-3 text-center font-black text-blue-800 border-r border-gray-200 bg-blue-50/30 text-sm">
                            {{ $p->intScore ?? '-' }}
                        </td>
                        <td class="p-3 font-extrabold text-sky-900 bg-sky-50/30">
                            {{ $p->txtAchievement ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400">Tidak ada data KPI.</td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Summary Total Row -->
                <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-black text-xs text-gray-900">
                    <tr>
                        <td colspan="3" class="p-3 text-right border-r border-gray-200 uppercase tracking-wider">Total</td>
                        <td class="p-3 text-center border-r border-gray-200 text-sm font-black text-[#006838]">{{ $totalWeight }}</td>
                        <td colspan="2" class="border-r border-gray-200"></td>
                        <td class="p-3 text-center border-r border-gray-200 text-sm font-black text-blue-900 bg-blue-100/50">{{ $totalScore }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Employee Performance Scorecard Grid -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <h3 class="text-sm font-extrabold text-gray-900 m-0">Ringkasan Kinerja Per Employee</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($employees as $emp)
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 space-y-3">
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
                        <strong class="text-gray-800 text-sm">{{ $emp['totalWeight'] }}</strong>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-gray-100">
                        <span class="block text-[10px] text-gray-400 uppercase font-bold">Avg Score</span>
                        <strong class="text-amber-600 text-sm">{{ $emp['avgScore'] }} <small class="text-gray-400">/ 5</small></strong>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                        <span class="text-gray-500">Avg Exposure Progress</span>
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

</div>
@endsection