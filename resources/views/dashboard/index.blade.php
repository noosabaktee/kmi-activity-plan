@extends('layouts.app', [
    'title' => 'Dashboard - KMI Activity Plan',
    'pageTitle' => 'DASHBOARD KPI & ACTIVITY',
    'pageSubtitle' => '<span>Manufacturing Development & Planning (MDP)</span> &bull; <span>Year ' . date('Y') . '</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header Greeting & Quick Action Banner -->
    <div class="bg-gradient-to-r from-[#006838] via-[#005a30] to-[#004d29] rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-48 h-48 rounded-full bg-white/5 blur-xl"></div>
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-300 text-xs font-semibold backdrop-blur-xs">
                <i class="fa-solid fa-sparkles text-[#8CC63F]"></i>
                <span>Selamat Datang, {{ $authUser->txtEmployeeName }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-black tracking-tight leading-tight m-0">
                Department Performance & Activity Plan
            </h1>
            <p class="text-xs md:text-sm text-emerald-100/90 max-w-xl m-0 leading-relaxed">
                Pantau realisasi KPI tahunan, progres exposure stage S-Curve, serta aktivitas kerja harian seluruh employee sub-departemen.
            </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-3">
            <a href="{{ route('projects.create') }}" class="px-4 py-2.5 rounded-xl bg-[#8CC63F] hover:bg-[#7ab233] text-[#004d29] font-bold text-xs shadow-md transition flex items-center gap-2 no-underline">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Project</span>
            </a>
            <a href="{{ route('reports.daily-tasks') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition flex items-center gap-2 no-underline backdrop-blur-xs">
                <i class="fa-solid fa-table-cells"></i>
                <span>Daily Task</span>
            </a>
            <a href="{{ route('reports.daily-plans') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition flex items-center gap-2 no-underline backdrop-blur-xs">
                <i class="fa-solid fa-calendar-week"></i>
                <span>Weekly Plan</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Projects -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Projects</span>
                    <h3 class="text-2xl md:text-3xl font-black text-[#006838] mt-1 m-0">{{ $totalProjects }}</h3>
                    <p class="text-[11px] text-gray-500 font-medium m-0 mt-0.5">Total Weight: <strong class="text-gray-800">{{ $totalWeight }}</strong></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#EBF5E9] text-[#006838] flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
            </div>
        </div>

        <!-- Cumulative Exposure Actual -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Exposure</span>
                    <h3 class="text-2xl md:text-3xl font-black text-[#8CC63F] mt-1 m-0">{{ $avgActual }}%</h3>
                    <p class="text-[11px] text-gray-500 font-medium m-0 mt-0.5">Planned: <strong class="text-gray-800">100%</strong></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Avg KPI Score -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Avg KPI Score</span>
                    <h3 class="text-2xl md:text-3xl font-black text-amber-500 mt-1 m-0">{{ $avgScore }} <span class="text-xs font-semibold text-gray-400">/ 5</span></h3>
                    <p class="text-[11px] text-gray-500 font-medium m-0 mt-0.5">Skala Target 1 s/d 5</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="bg-white p-5 rounded-2xl border border-[#DDE5DD] shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Employees</span>
                    <h3 class="text-2xl md:text-3xl font-black text-[#006838] mt-1 m-0">{{ $employees->count() }}</h3>
                    <p class="text-[11px] text-gray-500 font-medium m-0 mt-0.5">4 Sub Departments</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#EBF5E9] text-[#006838] flex items-center justify-center text-xl shadow-xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- S-Curve Highlight & Sub-Department Progress Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Exposure S-Curve Quick Preview -->
        <div class="lg:col-span-8 bg-white p-5 md:p-6 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Exposure S-Curve Overview</h3>
                    <p class="text-xs text-gray-500 m-0">Planned vs Actual cumulative progress kurva S.</p>
                </div>
                <a href="{{ route('exposure.index') }}" class="text-xs font-bold text-[#006838] hover:underline flex items-center gap-1">
                    <span>Lihat Detail S-Curve</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="h-64 w-full relative">
                <canvas id="dashboardExposureChart"></canvas>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-600">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#8CC63F] inline-block"></span> Actual (%)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#6D5BD0] inline-block"></span> Planned (%)</span>
                </div>
                <span class="font-medium text-gray-500">Updated: {{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <!-- Right: Sub-Departments Breakdown -->
        <div class="lg:col-span-4 bg-white p-5 md:p-6 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col justify-between">
            <div class="mb-4">
                <h3 class="text-base font-extrabold text-gray-900 m-0">Sub Department MDP</h3>
                <p class="text-xs text-gray-500 m-0">Distribusi Project & KPI sub-unit.</p>
            </div>

            <div class="space-y-3 flex-1">
                @foreach ($subDepartments as $sd)
                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 hover:border-emerald-200 hover:bg-[#EBF5E9]/50 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-[#006838]">{{ $sd->txtSubDepartmentCode }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-white text-gray-700 font-bold text-[10px] border border-gray-200">
                                {{ $sd->projects_count }} Projects
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 font-medium mt-1 truncate">{{ $sd->txtSubDepartmentName }}</p>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 mt-2 border-t border-gray-100">
                <a href="{{ route('projects.index') }}" class="w-full py-2.5 rounded-xl bg-gray-100 hover:bg-[#006838] hover:text-white text-gray-800 font-bold text-xs text-center block transition no-underline">
                    Lihat Semua Project
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Daily Tasks & Weekly Plans Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Recent Tasks List -->
        <div class="lg:col-span-7 bg-white p-5 md:p-6 rounded-3xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Aktivitas Daily Task Terbaru</h3>
                    <p class="text-xs text-gray-500 m-0">Catatan kerja harian dari employee.</p>
                </div>
                <a href="{{ route('reports.daily-tasks') }}" class="text-xs font-bold text-[#006838] hover:underline flex items-center gap-1">
                    <span>Buka Spreadsheet</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

            <div class="divide-y divide-gray-100 overflow-x-auto">
                @forelse ($recentTasks as $task)
                    <div class="py-3 flex items-start justify-between gap-3 text-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-[#EBF5E9] text-[#006838] font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                {{ $task->dtmTaskDate?->format('d') }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 m-0">{{ $task->txtActivityDescription }}</p>
                                <p class="text-[11px] text-gray-500 m-0">
                                    {{ $task->user?->txtEmployeeName }} &bull; <span class="text-[#006838] font-semibold">{{ $task->project?->txtProjectName ?? 'Routine' }}</span>
                                    @if ($task->subProject)
                                        <span class="text-gray-400">/ {{ $task->subProject->txtSubProjectName }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $task->txtTaskStatus === 'Completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $task->txtTaskStatus }}
                            </span>
                            <p class="text-[10px] text-gray-400 font-medium m-0 mt-0.5">{{ $task->floatDurationHours }} Jam</p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-gray-400">
                        <i class="fa-solid fa-inbox text-2xl mb-1 block"></i>
                        Belum ada daily task yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Weekly Plan Cards -->
        <div class="lg:col-span-5 bg-white p-5 md:p-6 rounded-3xl border border-[#DDE5DD] shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Weekly Plan Cards</h3>
                    <p class="text-xs text-gray-500 m-0">Perencanaan kerja mingguan Senin-Jumat.</p>
                </div>
                <a href="{{ route('reports.daily-plans') }}" class="text-xs font-bold text-[#006838] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($recentWeeklyPlans as $plan)
                    <a href="{{ route('daily-plans.show', $plan) }}" class="block p-4 rounded-2xl bg-gray-50 hover:bg-emerald-50/60 border border-gray-200 hover:border-emerald-300 transition no-underline text-gray-800">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-black text-[#006838]">{{ $plan->txtWeekTitle }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-emerald-700 border border-emerald-200">
                                {{ $plan->activities->count() }} Aktivitas
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>PIC: <strong>{{ $plan->user?->txtEmployeeName ?? '-' }}</strong></span>
                            <span>{{ $plan->dtmWeekStartDate?->format('d M') }} - {{ $plan->dtmWeekEndDate?->format('d M Y') }}</span>
                        </div>
                    </a>
                @empty
                    <div class="py-8 text-center text-xs text-gray-400">
                        <i class="fa-solid fa-calendar-xmark text-2xl mb-1 block"></i>
                        Belum ada weekly plan.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- JSON Payload for Dashboard S-Curve -->
<script type="application/json" id="exposurePayloadJson">@json($exposurePayload)</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const payloadElement = document.getElementById('exposurePayloadJson');
    const canvas = document.getElementById('dashboardExposureChart');
    if (!payloadElement || !canvas || !window.Chart) return;

    const payload = JSON.parse(payloadElement.textContent || '{}');
    const projects = payload.projects || [];

    // Simple aggregate S-Curve data points by month
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const planned = [10, 20, 30, 45, 55, 65, 75, 85, 90, 95, 98, 100];
    const actual = [10, 18, 28, 40, 52, 60, 72, 78.5, null, null, null, null];

    new window.Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Actual Cumulative (%)',
                    data: actual,
                    borderColor: '#8CC63F',
                    backgroundColor: 'rgba(140, 198, 63, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#8CC63F',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Planned Cumulative (%)',
                    data: planned,
                    borderColor: '#6D5BD0',
                    borderWidth: 2.5,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#6D5BD0',
                    pointBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}%`
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { callback: (v) => `${v}%`, font: { size: 10 } },
                    grid: { color: '#EBF0EB' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
