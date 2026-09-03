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

            <div style="position: relative; height: 360px; width: 100%;">
                <canvas id="projectProgressChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Distribusi Status & Kesehatan Project (Span 2 of 6 Cols) -->
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

            <div style="position: relative; height: 260px; width: 100%; display: flex; align-items: center; justify-content: center;">
                <canvas id="projectStatusChart"></canvas>
            </div>

            <!-- Legend Pills -->
            <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-gray-100 font-semibold">
                <div class="flex items-center gap-2 text-emerald-800">
                    <span class="w-3 h-3 rounded-md bg-[#006838] shrink-0"></span>
                    <span>Selesai (100%): <strong>{{ $statusCounts['completed'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-emerald-700">
                    <span class="w-3 h-3 rounded-md bg-[#8CC63F] shrink-0"></span>
                    <span>On Track (&ge;75%): <strong>{{ $statusCounts['on_track'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-amber-700">
                    <span class="w-3 h-3 rounded-md bg-[#FBBF24] shrink-0"></span>
                    <span>In Progress (40-74%): <strong>{{ $statusCounts['in_progress'] }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-rose-700">
                    <span class="w-3 h-3 rounded-md bg-[#F87171] shrink-0"></span>
                    <span>Perhatian (&lt;40%): <strong>{{ $statusCounts['needs_action'] }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Chart 3: Tren Aktivitas Daily Task Sepanjang Bulan (Span 3 of 6 Cols) -->
        <div class="lg:col-span-3 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-trend-up text-blue-600"></i>
                        <span>Tren Aktivitas Logbook Daily Task (Bulan {{ $monthName }})</span>
                    </h3>
                    <p class="text-xs text-gray-500 m-0">Jumlah task yang dikerjakan oleh tim setiap tanggal pada bulan ini.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">
                        <i class="fa-solid fa-calendar-day"></i> 1 s/d {{ count($chartTrendLabels) }} {{ $monthName }}
                    </span>
                </div>
            </div>

            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Progress & Beban Kerja per Sub-Department (Span 3 of 6 Cols) -->
        <div class="lg:col-span-3 bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-sitemap text-purple-600"></i>
                    <span>Kinerja per Sub-Department</span>
                </h3>
                <p class="text-xs text-gray-500 m-0">Rata-rata actual progress & total bobot per sub-dept.</p>
            </div>

            <div style="position: relative; height: 300px; width: 100%;">
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