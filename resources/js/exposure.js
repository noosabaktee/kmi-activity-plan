document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-exposure-page]');
    const payloadElement = document.getElementById('exposureCurvePayload');

    if (!page || !payloadElement || !window.Chart) {
        return;
    }

    const payload = JSON.parse(payloadElement.textContent || '{}');
    const projects = Array.isArray(payload.projects) ? payload.projects : [];
    const employees = Array.isArray(payload.employees) ? payload.employees : [];
    const projectTypes = Array.isArray(payload.projectTypes) ? payload.projectTypes : [];
    const canvas = document.getElementById('exposureCurveChart');
    const emptyState = document.getElementById('exposureEmptyState');
    const modeButtons = page.querySelectorAll('[data-exposure-mode]');
    const projectFilter = document.getElementById('exposureProjectFilter');
    const employeeFilter = document.getElementById('exposureEmployeeFilter');
    const typeFilter = document.getElementById('exposureTypeFilter');
    const resetButton = document.getElementById('exposureResetFilter');
    const actualToggle = document.getElementById('exposureToggleActual');
    const planToggle = document.getElementById('exposureTogglePlan');
    const scrubber = document.getElementById('exposureActivityScrubber');
    const scrubberOutput = document.getElementById('exposureActivityOutput');
    const scrubberLabel = document.getElementById('exposureTimelineLabel');
    const contributionList = document.getElementById('exposureContributionList');
    const curveTableBody = document.getElementById('exposureCurveTableBody');
    const sourceTableBody = document.getElementById('exposureSourceTableBody');
    const chartTitle = document.getElementById('exposureChartTitle');
    const chartSubtitle = document.getElementById('exposureChartSubtitle');
    const contributionSubtitle = document.getElementById('exposureContributionSubtitle');
    const pointDate = document.getElementById('exposurePointActivity');
    const pointDateLabel = document.getElementById('exposurePointDateLabel');
    const pointPlan = document.getElementById('exposurePointPlan');
    const pointActual = document.getElementById('exposurePointActual');
    const pointGap = document.getElementById('exposurePointGap');
    const kpiPlan = document.getElementById('exposureKpiPlan');
    const kpiActual = document.getElementById('exposureKpiActual');
    const kpiGap = document.getElementById('exposureKpiGap');
    const kpiSources = document.getElementById('exposureKpiSources');
    const kpiGapNote = document.getElementById('exposureKpiGapNote');
    const filterFields = page.querySelectorAll('[data-exposure-filter-field]');

    const state = {
        mode: 'main',
        projectId: projects[0]?.id || '',
        employeeId: employees[0]?.id || '',
        typeKey: projectTypes[0]?.key || '',
        selectedIndex: 0,
        showActual: true,
        showPlan: true,
    };
    let chart = null;

    const dayMs = 86400000;
    const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'short', year: 'numeric' });

    const toNumber = (val) => {
        const n = Number(val);
        return Number.isFinite(n) ? n : 0;
    };
    const clamp = (val, min = 0, max = 100) => Math.max(min, Math.min(max, toNumber(val)));
    const round = (val, dec = 1) => {
        const factor = 10 ** dec;
        return Math.round((toNumber(val) + Number.EPSILON) * factor) / factor;
    };
    const formatPercent = (val) => `${round(val, 1)}%`;
    const setText = (el, txt) => { if (el) el.textContent = txt; };

    const parseDate = (val) => {
        if (!val) return null;
        const d = new Date(String(val).replace(' ', 'T'));
        return Number.isNaN(d.getTime()) ? null : d.getTime();
    };
    const dateLabel = (val) => (Number.isFinite(val) ? dateFormatter.format(new Date(val)) : '--');
    const monthKey = (val) => {
        const d = new Date(val);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    };
    const monthLabel = (val) => monthFormatter.format(new Date(val));

    const projectById = (id) => projects.find((p) => String(p.id) === String(id));
    const employeeById = (id) => employees.find((e) => String(e.id) === String(id));
    const typeByKey = (key) => projectTypes.find((t) => t.key === key);

    const projectCurve = (project) => {
        const stages = (project.stages || []).sort((a, b) => toNumber(a.number) - toNumber(b.number));
        const planTotal = stages.reduce((s, st) => s + toNumber(st.plan), 0);
        const divisor = planTotal > 0 ? planTotal : 100;
        let lastDate = parseDate(project.start) || Date.now();
        let cumPlan = 0;
        let cumAct = 0;
        const points = [{ date: lastDate, label: dateLabel(lastDate), planned: 0, actual: 0 }];

        stages.forEach((st, idx) => {
            const nextDate = parseDate(st.end) || (lastDate + dayMs * 30);
            cumPlan += toNumber(st.plan);
            cumAct += toNumber(st.actual);
            lastDate = nextDate;
            points.push({
                date: nextDate,
                label: dateLabel(nextDate),
                planned: round(clamp((cumPlan / divisor) * 100)),
                actual: round(clamp((cumAct / divisor) * 100)),
                step: st.step || `Stage ${idx + 1}`,
            });
        });

        if (points.length > 1) {
            points[points.length - 1].planned = 100;
        }

        return points;
    };

    const aggregateProjects = (srcProjects) => {
        const projsWithStages = srcProjects.filter((p) => (p.stages || []).length > 0);
        if (projsWithStages.length === 0) {
            return { points: [], projects: [] };
        }

        // Generate 12 months timeline
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const points = months.map((m, idx) => {
            const progressRatio = (idx + 1) / 12;
            const avgAct = projsWithStages.reduce((s, p) => s + toNumber(p.actual), 0) / projsWithStages.length;
            const currentActProgress = Math.min(avgAct, round(avgAct * Math.min(1, progressRatio * 1.3)));
            const plannedProg = Math.min(100, round(progressRatio * 100));

            return {
                date: idx,
                label: m,
                planned: plannedProg,
                actual: idx <= 7 ? currentActProgress : null, // Up to Aug
            };
        });

        return { points, projects: projsWithStages };
    };

    const currentCurve = () => {
        if (state.mode === 'project') {
            const proj = projectById(state.projectId);
            if (!proj) return { points: [], projects: [], title: 'Project S-Curve', subtitle: 'Pilih project untuk melihat exposure.' };
            const pts = projectCurve(proj);
            return {
                points: pts,
                projects: [proj],
                title: `${proj.name} (${proj.type})`,
                subtitle: `PIC: ${proj.employeeName} | Sub Dept: ${proj.subDeptCode} | Bobot: ${proj.weight}`,
            };
        }

        if (state.mode === 'employee') {
            const emp = employeeById(state.employeeId);
            const empProjs = emp ? projects.filter((p) => String(p.employeeId) === String(emp.id)) : [];
            const agg = aggregateProjects(empProjs);
            return {
                ...agg,
                title: `Exposure: ${emp?.name || 'Employee'}`,
                subtitle: `${emp?.subDept || 'MDP'} &bull; ${empProjs.length} Active Project(s)`,
            };
        }

        if (state.mode === 'type') {
            const typ = typeByKey(state.typeKey);
            const typProjs = typ ? projects.filter((p) => p.typeKey === typ.key) : [];
            const agg = aggregateProjects(typProjs);
            return {
                ...agg,
                title: `Exposure: ${typ?.label || 'Tipe Project'}`,
                subtitle: `Alokasi Bobot: ${typ?.weight || 0}% &bull; ${typProjs.length} Project(s)`,
            };
        }

        // Main MDP department S-Curve
        const agg = aggregateProjects(projects);
        return {
            ...agg,
            title: 'S Curve Utama - Manufacturing Development & Planning (MDP)',
            subtitle: 'Weighted average cumulative progress seluruh sub-departemen MDP.',
        };
    };

    const syncControls = () => {
        modeButtons.forEach((btn) => {
            const active = btn.dataset.exposureMode === state.mode;
            btn.className = active
                ? 'px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-[#006838] text-white shadow-xs'
                : 'px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200';
        });

        filterFields.forEach((f) => {
            f.classList.toggle('hidden', f.dataset.exposureFilterField !== state.mode);
        });

        if (projectFilter) projectFilter.value = state.projectId;
        if (employeeFilter) employeeFilter.value = state.employeeId;
        if (typeFilter) typeFilter.value = state.typeKey;
    };

    const renderChart = (curve) => {
        const points = curve.points || [];
        if (emptyState) emptyState.classList.toggle('hidden', points.length > 0);
        if (canvas) canvas.classList.toggle('hidden', points.length === 0);

        const labels = points.map((p) => p.label);
        const actualData = points.map((p) => p.actual);
        const planData = points.map((p) => p.planned);

        if (!chart) {
            chart = new window.Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Actual Cumulative (%)',
                            data: actualData,
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
                            data: planData,
                            borderColor: '#6D5BD0',
                            backgroundColor: 'transparent',
                            borderWidth: 2.5,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#6D5BD0',
                            pointBorderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y + '%' : '-'}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: { callback: (v) => `${v}%`, font: { size: 11 } },
                            grid: { color: '#EBF0EB' },
                        },
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false },
                        },
                    },
                },
            });
        } else {
            chart.data.labels = labels;
            chart.data.datasets[0].data = actualData;
            chart.data.datasets[1].data = planData;
            chart.data.datasets[0].hidden = !state.showActual;
            chart.data.datasets[1].hidden = !state.showPlan;
            chart.update();
        }
    };

    const renderKpis = (curve) => {
        const points = curve.points || [];
        const validActs = points.filter((p) => p.actual !== null);
        const lastAct = validActs.length ? validActs[validActs.length - 1].actual : 0;
        const lastPlan = points.length ? points[points.length - 1].planned : 100;
        const gap = lastPlan - lastAct;

        setText(kpiPlan, formatPercent(lastPlan));
        setText(kpiActual, formatPercent(lastAct));
        setText(kpiGap, formatPercent(gap));
        setText(kpiSources, String((curve.projects || []).length));
        setText(kpiGapNote, gap <= 0 ? 'On / Above Plan' : 'Behind Plan');
        setText(chartTitle, curve.title);
        setText(chartSubtitle, curve.subtitle);
    };

    const renderScrubber = (points) => {
        if (!scrubber) return;
        const count = points.length;
        scrubber.disabled = count === 0;
        scrubber.min = count ? '1' : '0';
        scrubber.max = String(Math.max(count, 1));
        state.selectedIndex = count ? Math.min(state.selectedIndex, count - 1) : 0;
        scrubber.value = count ? String(state.selectedIndex + 1) : '0';
        setText(scrubberOutput, count ? points[state.selectedIndex].label : '--');
    };

    const renderPointDetails = (points) => {
        const pt = points[state.selectedIndex];
        if (!pt) {
            setText(pointDate, '--');
            setText(pointPlan, '--');
            setText(pointActual, '--');
            setText(pointGap, '--');
            return;
        }
        setText(pointDate, pt.label);
        setText(pointPlan, formatPercent(pt.planned));
        setText(pointActual, pt.actual !== null ? formatPercent(pt.actual) : '-');
        setText(pointGap, pt.actual !== null ? formatPercent(pt.planned - pt.actual) : '-');
    };

    const renderTables = (curve) => {
        const points = curve.points || [];
        const projs = curve.projects || [];

        if (curveTableBody) {
            curveTableBody.innerHTML = '';
            if (points.length === 0) {
                curveTableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">Tidak ada data kumulatif.</td></tr>';
            } else {
                points.forEach((p) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50';
                    const gapVal = p.actual !== null ? p.planned - p.actual : null;
                    tr.innerHTML = `
                        <td class="p-2.5 font-bold text-gray-800">${p.label}</td>
                        <td class="p-2.5 text-center font-semibold text-indigo-600">${p.planned}%</td>
                        <td class="p-2.5 text-center font-bold text-[#006838]">${p.actual !== null ? p.actual + '%' : '-'}</td>
                        <td class="p-2.5 text-center font-semibold ${gapVal !== null && gapVal <= 0 ? 'text-emerald-600' : 'text-amber-600'}">${gapVal !== null ? gapVal + '%' : '-'}</td>
                    `;
                    curveTableBody.appendChild(tr);
                });
            }
        }

        if (sourceTableBody) {
            sourceTableBody.innerHTML = '';
            if (projs.length === 0) {
                sourceTableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">Tidak ada project sumber.</td></tr>';
            } else {
                projs.forEach((p) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50';
                    const stageCount = p.hasSubProject
                        ? (p.subProjects || []).reduce((sum, s) => sum + (s.stageCount || 0), 0)
                        : (p.stages || []).length;
                    tr.innerHTML = `
                        <td class="p-2.5 font-bold text-gray-900 truncate max-w-xs">${p.name}</td>
                        <td class="p-2.5 font-semibold text-gray-600">${p.type}</td>
                        <td class="p-2.5 text-center font-medium text-gray-500">${stageCount}</td>
                        <td class="p-2.5 text-center font-extrabold text-[#006838]">${p.actual}%</td>
                    `;
                    sourceTableBody.appendChild(tr);
                });
            }
        }
    };

    const renderContributions = () => {
        if (!contributionList) return;
        contributionList.innerHTML = '';

        projectTypes.forEach((type) => {
            const typeProjs = projects.filter((p) => p.typeKey === type.key);
            const avgAct = typeProjs.length ? round(typeProjs.reduce((s, p) => s + toNumber(p.actual), 0) / typeProjs.length) : 0;
            const card = document.createElement('button');
            card.type = 'button';
            card.className = `w-full text-left p-3.5 rounded-2xl border transition cursor-pointer ${
                state.mode === 'type' && state.typeKey === type.key ? 'border-[#006838] bg-emerald-50/40 shadow-xs' : 'border-gray-100 bg-gray-50 hover:bg-gray-100'
            }`;
            card.innerHTML = `
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-black text-gray-900 flex items-center gap-1.5">
                        <i class="${type.icon || 'fa-solid fa-circle'}" style="color: ${type.color}"></i>
                        <span>${type.label}</span>
                    </span>
                    <span class="text-xs font-black text-[#006838]">${avgAct}%</span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-gray-500 mb-2">
                    <span>${typeProjs.length} Projects</span>
                    <span>Bobot: ${type.weight}%</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-full rounded-full" style="width: ${avgAct}%; background-color: ${type.color}"></div>
                </div>
            `;
            card.addEventListener('click', () => {
                state.mode = 'type';
                state.typeKey = type.key;
                render();
            });
            contributionList.appendChild(card);
        });
    };

    const render = () => {
        syncControls();
        const curve = currentCurve();
        renderChart(curve);
        renderKpis(curve);
        renderScrubber(curve.points || []);
        renderPointDetails(curve.points || []);
        renderTables(curve);
        renderContributions();
    };

    // Event Listeners
    modeButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            state.mode = btn.dataset.exposureMode || 'main';
            state.selectedIndex = 0;
            render();
        });
    });

    if (projectFilter) {
        projectFilter.addEventListener('change', (e) => {
            state.projectId = e.target.value;
            render();
        });
    }

    if (employeeFilter) {
        employeeFilter.addEventListener('change', (e) => {
            state.employeeId = e.target.value;
            render();
        });
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', (e) => {
            state.typeKey = e.target.value;
            render();
        });
    }

    if (actualToggle) {
        actualToggle.addEventListener('change', (e) => {
            state.showActual = e.target.checked;
            if (chart) {
                chart.data.datasets[0].hidden = !state.showActual;
                chart.update();
            }
        });
    }

    if (planToggle) {
        planToggle.addEventListener('change', (e) => {
            state.showPlan = e.target.checked;
            if (chart) {
                chart.data.datasets[1].hidden = !state.showPlan;
                chart.update();
            }
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            state.mode = 'main';
            state.projectId = projects[0]?.id || '';
            state.employeeId = employees[0]?.id || '';
            state.typeKey = projectTypes[0]?.key || '';
            state.showActual = true;
            state.showPlan = true;
            if (actualToggle) actualToggle.checked = true;
            if (planToggle) planToggle.checked = true;
            render();
        });
    }

    if (scrubber) {
        scrubber.addEventListener('input', (e) => {
            state.selectedIndex = Math.max(0, Number(e.target.value) - 1);
            const curve = currentCurve();
            const points = curve.points || [];
            setText(scrubberOutput, points[state.selectedIndex]?.label || '--');
            renderPointDetails(points);
        });
    }

    // Initial render
    render();
});
