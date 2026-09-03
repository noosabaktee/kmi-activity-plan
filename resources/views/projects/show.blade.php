@extends('layouts.app', [
'title' => $project->txtProjectName . ' - KMI Activity Plan',
'pageTitle' => 'DETAIL PROJECT',
'pageSubtitle' => '<span>' . $project->txtProjectCode . '</span> &bull; <span>' . $project->txtProjectName . '</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white text-[#006838] border border-gray-200 flex items-center justify-center text-xl shadow-xs">
                @if ($project->bitHasSubProject)
                <i class="fa-solid fa-layer-group text-purple-600"></i>
                @else
                <i class="fa-solid fa-cube text-[#006838]"></i>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">{{ $project->txtProjectName }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold text-white" style="background-color: {{ $project->projectType?->txtColor ?? '#006838' }}">
                        {{ $project->projectType?->txtProjectTypeCode ?? 'IPP' }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 m-0 mt-0.5">
                    {{ $project->txtProjectCode }} &bull; {{ $project->subDepartment?->txtSubDepartmentName ?? 'MDP' }} &bull; PIC: <strong class="text-gray-800">{{ $project->user?->txtEmployeeName ?? 'Unassigned' }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('projects.index') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-2 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition flex items-center gap-2 no-underline">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Project</span>
            </a>
        </div>
    </div>

    <!-- Quick Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">KPI Weight (Bobot)</span>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ $project->floatWeight }}</div>
            <p class="text-[10px] text-gray-500 m-0">Level: <strong>{{ $project->txtKpiLevel }}</strong></p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">KPI Score</span>
            <div class="text-2xl font-black text-amber-500 mt-1">
                {{ $project->intScore ?? '-' }} <span class="text-xs text-gray-400 font-semibold">/ 5</span>
            </div>
            <p class="text-[10px] text-gray-500 m-0">Skala Target 1 - 5</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Achievement</span>
            <div class="text-lg font-black text-[#006838] mt-1 truncate">{{ $project->txtAchievement ?: '-' }}</div>
            <p class="text-[10px] text-gray-500 m-0">Realisasi Target</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exposure Actual</span>
            <div class="text-2xl font-black text-[#8CC63F] mt-1">{{ $project->floatActual }}%</div>
            <p class="text-[10px] text-gray-500 m-0">Planned: 100%</p>
        </div>
    </div>

    <!-- Deliverable & KPI Target Grading Scale Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div class="md:col-span-6 bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-3">
            <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-flag text-emerald-600"></i>
                <span>Deliverable & Output</span>
            </h3>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-sm font-semibold text-gray-800">
                {{ $project->txtDeliverable ?: 'Belum ada deliverable yang ditentukan.' }}
            </div>
            @if ($project->txtDescription)
            <p class="text-xs text-gray-600 leading-relaxed">{{ $project->txtDescription }}</p>
            @endif
        </div>

        <div class="md:col-span-6 bg-white p-5 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-3">
            <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-list-ol text-amber-500"></i>
                <span>Target Skala Grade (1 s/d 5)</span>
            </h3>
            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200 text-xs font-mono text-gray-800 whitespace-pre-line leading-relaxed">
                {{ $project->txtTargetSkalaGrade ?: "1. 80%\n2. 85%\n3. 90%\n4. 95%\n5. 100%" }}
            </div>
        </div>
    </div>

    <!-- S-Curve Chart for this Project -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0">Kurva Exposure S-Curve Project</h3>
                <p class="text-xs text-gray-500 m-0">Planned vs Actual cumulative progress tahapan kerja.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#8CC63F] inline-block"></span> Actual</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[#6D5BD0] inline-block"></span> Planned</span>
            </div>
        </div>

        <div class="h-64 w-full relative">
            <canvas id="projectCurveCanvas"></canvas>
        </div>
    </div>

    <!-- Sub-Projects (if Project with Sub Projects) -->
    @if ($project->bitHasSubProject)
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-purple-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-purple-600"></i>
                    <span>Daftar Sub Projects (Akumulasi Exposure)</span>
                </h3>
                <p class="text-xs text-gray-500 m-0">Exposure project utama diakumulasi berdasarkan bobot masing-masing sub project.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-purple-50/50 border-b border-purple-100 text-purple-900 font-bold uppercase">
                    <tr>
                        <th class="p-3">Nama Sub Project</th>
                        <th class="p-3">Periode</th>
                        <th class="p-3 text-center">Bobot Sub (%)</th>
                        <th class="p-3 text-center">Tahapan S-Curve</th>
                        <th class="p-3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($project->subProjects as $sub)
                    <tr class="hover:bg-purple-50/20 transition">
                        <td class="p-3 font-bold text-gray-900">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-cube text-purple-500"></i>
                                <span>{{ $sub->txtSubProjectName }}</span>
                            </div>
                            @if ($sub->txtDeliverable)
                            <p class="text-[11px] text-gray-500 m-0 mt-0.5"><strong class="text-gray-700">Output:</strong> {{ $sub->txtDeliverable }}</p>
                            @endif
                        </td>
                        <td class="p-3 text-gray-600 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 text-xs">
                                <i class="fa-regular fa-calendar text-purple-400"></i>
                                <span>{{ $sub->dtmStartDate?->format('d M Y') ?? '-' }} s/d {{ $sub->dtmEndDate?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="p-3 text-center font-extrabold text-gray-800">{{ $sub->floatWeight }}%</td>
                        <td class="p-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                                <i class="fa-solid fa-chart-gantt text-[10px]"></i>
                                <span>{{ $sub->stages->count() }} Stages</span>
                            </span>
                        </td>
                        <td class="p-3 min-w-[140px]">
                            <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                <span class="text-gray-500">Progress</span>
                                <span class="text-purple-700">{{ $sub->floatProgress }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full bg-purple-600 rounded-full" style="width: {{ min(100, $sub->floatProgress) }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Stages Breakdown Table -->
    <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        <h3 class="text-sm font-extrabold text-gray-900 m-0">Rincian Tahapan Project Stages</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">#</th>
                        <th class="p-3">Tahapan (Stage Step)</th>
                        <th class="p-3">Periode (Start - End)</th>
                        <th class="p-3 text-center w-24">Planned (%)</th>
                        <th class="p-3 text-center w-24">Actual (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($projectPayload['stages'] as $st)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $st['number'] }}</td>
                        <td class="p-3 font-bold text-gray-800">{{ $st['step'] }}</td>
                        <td class="p-3 text-gray-500">{{ $st['start'] ?? '-' }} s/d {{ $st['end'] ?? '-' }}</td>
                        <td class="p-3 text-center font-bold text-indigo-600">{{ $st['plan'] }}%</td>
                        <td class="p-3 text-center font-bold text-[#006838]">{{ $st['actual'] }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400">Belum ada tahapan stage yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script type="application/json" id="projectPayloadJson">
    @json($projectPayload)
</script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const payloadElement = document.getElementById('projectPayloadJson');
        const canvas = document.getElementById('projectCurveCanvas');
        if (!payloadElement || !canvas || !window.Chart) return;

        const payload = JSON.parse(payloadElement.textContent || '{}');
        const stages = payload.stages || [];

        const labels = stages.map(s => s.step);
        let cumPlan = 0;
        let cumAct = 0;
        const planData = [];
        const actData = [];

        stages.forEach(s => {
            cumPlan += Number(s.plan || 0);
            cumAct += Number(s.actual || 0);
            planData.push(Math.min(100, cumPlan));
            actData.push(Math.min(100, cumAct));
        });

        if (stages.length === 0) {
            labels.push('Start', 'End');
            planData.push(0, 100);
            actData.push(0, payload.actual || 0);
        }

        new window.Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Actual Cumulative (%)',
                        data: actData,
                        borderColor: '#8CC63F',
                        backgroundColor: 'rgba(140, 198, 63, 0.15)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#8CC63F',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Planned Cumulative (%)',
                        data: planData,
                        borderColor: '#6D5BD0',
                        borderWidth: 2.5,
                        borderDash: [4, 4],
                        fill: false,
                        tension: 0.3,
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
                    legend: {
                        display: false
                    },
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
                        ticks: {
                            callback: (v) => `${v}%`,
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            color: '#EBF0EB'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            },
                            maxRotation: 20
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection