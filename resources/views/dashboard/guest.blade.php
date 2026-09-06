@extends('layouts.app', [
'title' => 'Dashboard Monitoring - KMI Activity Plan',
'hideSidebar' => true,
'hideTopbar' => true,
])

@section('content')
<div class="space-y-6 w-full">

    <!-- Guest Top Header Bar -->
    <div class="bg-white p-4 md:px-6 md:py-4 rounded-3xl border border-[#DDE5DD] shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center shadow-xs border border-gray-200 p-1.5 shrink-0">
                <img src="{{ asset('images/KDC.png') }}" alt="Kalbe" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fa-solid fa-leaf text-[#006838] text-xl\'></i>'">
            </div>
            <div>
                <h1 class="text-base font-extrabold text-[#006838] tracking-tight leading-tight m-0">KMI ACTIVITY PLAN</h1>
                <p class="text-[11px] text-gray-500 font-medium m-0">Monitoring Perkembangan Project & Aktivitas Bulanan &bull; {{ $monthName }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-500 font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
                <i class="fa-regular fa-calendar text-[#8CC63F]"></i>
                <span>{{ now()->translatedFormat('d M Y') }}</span>
            </div>
            <button type="button" onclick="openLoginModal()" class="px-5 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-right-to-bracket text-[#8CC63F]"></i>
                <span>Masuk / Login</span>
            </button>
        </div>
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