@extends('layouts.app', [
'title' => 'Daily Task Sheet - KMI Activity Plan',
'pageTitle' => 'DAILY TASK SPREADSHEET',
'pageSubtitle' => '<span>Handsontable Data Grid</span> &bull; <span>Performance & Daily Activity Tracker</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header Actions Toolbar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Spreadsheet Daily Task Employee</h2>
            <p class="text-xs text-gray-500 m-0">Input aktivitas harian, pilih project/sub-project otomatis, tambah baris, dan ekspor ke Excel.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button type="button" onclick="addNewRow()" class="px-4 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs border border-gray-200 shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-[#006838]"></i>
                <span>Tambah Baris</span>
            </button>
            <button type="button" onclick="saveHandsontableData()" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer" id="saveBtn">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
            <a href="{{ route('reports.daily-tasks.export-excel', request()->all()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-bold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-file-excel text-emerald-700"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs">
        <form method="GET" action="{{ route('reports.daily-tasks') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Employee</label>
                <select name="employee" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Employee</option>
                    @foreach ($employees as $emp)
                    <option value="{{ $emp->intUser_ID }}" {{ request('employee') == $emp->intUser_ID ? 'selected' : '' }}>
                        {{ $emp->txtEmployeeName }} ({{ $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Project</label>
                <select name="project" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Project ({{ count($projects) }})</option>
                    @foreach ($projects as $prj)
                    <option value="{{ $prj->intProject_ID }}" {{ request('project') == $prj->intProject_ID ? 'selected' : '' }}>
                        {{ $prj->txtProjectName }} {{ $prj->intUser_ID === $authUserId ? '★ (Project Saya)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
            </div>
        </form>
    </div>

    <!-- Status Alert Bar -->
    <div id="statusAlert" class="hidden p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs transition-all"></div>

    <!-- Handsontable Grid Shell -->
    <div class="handsontable-container-card p-4 bg-white">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                    <i class="fa-solid fa-table-cells mr-1.5 text-[#006838]"></i> Interactive Data Grid
                </span>
                <span class="text-xs text-gray-400 font-medium" id="rowCountLabel">Memuat baris...</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <span>Theme: <strong class="text-gray-700">Handsontable Modern</strong></span>
            </div>
        </div>

        <div id="hotTableContainer" class="w-full bg-white" style="height: 540px; min-height: 480px;"></div>
    </div>

    <!-- Help & Shortcuts note -->
    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 text-xs text-gray-600 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-lightbulb text-emerald-600 text-sm"></i>
            <span><strong>Tips:</strong> Klik sel untuk mengedit langsung. Status & Progress bar akan otomatis ter-render sesuai nilai. Klik kanan untuk menu context (tambah/hapus baris).</span>
        </div>
        <span class="font-mono text-[10px] text-gray-400">Handsontable v14 Theme</span>
    </div>

</div>

<!-- JSON Payloads -->
<script type="application/json" id="initialHotData">
    @json($handsontableData)
</script>
<script type="application/json" id="projectsLookupJson">
    @json($projectsLookup)
</script>
<script type="application/json" id="employeesLookupJson">
    @json($employeesLookup)
</script>

@push('scripts')
<script>
    let hotInstance = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const currentUserId = @json($authUserId);

    // Custom Status Pill Badge Renderer
    function statusBadgeRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        if (value === null || value === undefined || String(value).trim() === '') {
            td.innerHTML = '<span class="text-gray-300 text-xs">-</span>';
            td.className = (td.className || '') + ' htCenter htMiddle';
            return;
        }
        const status = String(value).trim();
        let badgeClass = 'ht-status-completed';
        let icon = 'fa-check';

        if (status === 'In Progress') {
            badgeClass = 'ht-status-inprogress';
            icon = 'fa-spinner fa-spin';
        } else if (status === 'Pending') {
            badgeClass = 'ht-status-pending';
            icon = 'fa-clock';
        } else if (status === 'Issue') {
            badgeClass = 'ht-status-issue';
            icon = 'fa-triangle-exclamation';
        }

        td.innerHTML = `<span class="ht-status-badge ${badgeClass} pointer-events-none select-none"><i class="fa-solid ${icon}"></i> ${status}</span>`;
        td.className = (td.className || '') + ' htCenter htMiddle';
    }

    // Custom Performance / Progress Bar Renderer (Matching Handsontable Demo)
    function progressBarRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        if (value === null || value === undefined || String(value).trim() === '') {
            td.innerHTML = '<span class="text-gray-300 text-xs">-</span>';
            td.className = (td.className || '').replace(/htTop|htBottom/g, '') + ' htCenter htMiddle';
            return;
        }
        const cleanVal = String(value).replace('%', '').trim();
        let val = parseFloat(cleanVal);
        if (isNaN(val)) val = 0;
        val = Math.min(100, Math.max(0, val));

        let barColor = '#006838'; // Kalbe primary green (100%)
        if (val < 40) {
            barColor = '#ef4444'; // Red (< 40%)
        } else if (val < 75) {
            barColor = '#3b82f6'; // Blue (< 75%)
        } else if (val < 100) {
            barColor = '#10b981'; // Emerald (< 100%)
        }

        td.innerHTML = `
        <div style="display: flex; align-items: center; width: 100%; gap: 8px; user-select: none; pointer-events: none; margin: 0;">
            <div style="flex: 1; height: 8px; min-height: 8px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden; position: relative;">
                <div style="position: absolute; top: 0; left: 0; height: 100%; width: ${val}%; background-color: ${barColor}; border-radius: 9999px; transition: width 0.3s ease;"></div>
            </div>
            <span style="font-size: 11px; font-weight: 700; color: #334155; min-width: 32px; text-align: right; font-variant-numeric: tabular-nums; line-height: 1;">${val}%</span>
        </div>
    `;
        td.className = (td.className || '').replace(/htTop|htBottom/g, '') + ' htMiddle';
    }

    // Custom Duration Badge Renderer
    function durationBadgeRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        if (value === null || value === undefined || String(value).trim() === '') {
            td.innerHTML = '<span class="text-gray-300 text-xs">-</span>';
            td.className = (td.className || '') + ' htCenter htMiddle';
            return;
        }
        const val = parseFloat(value) || 0;
        td.innerHTML = `<span class="ht-duration-badge pointer-events-none select-none">${val.toFixed(1)} jam</span>`;
        td.className = (td.className || '') + ' htCenter htMiddle';
    }

    // Custom Date Renderer
    function dateCellRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        const val = value ? String(value).trim() : '';
        if (!val) {
            td.innerHTML = '<span class="text-gray-300 text-xs">-</span>';
            td.className = (td.className || '') + ' htCenter htMiddle';
            return;
        }
        td.innerHTML = `<span class="ht-date-badge pointer-events-none select-none"><i class="fa-regular fa-calendar text-gray-400"></i> ${val}</span>`;
        td.className = (td.className || '') + ' htMiddle';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const rawData = JSON.parse(document.getElementById('initialHotData').textContent.trim() || '[]');
        const projectsLookup = JSON.parse(document.getElementById('projectsLookupJson').textContent.trim() || '[]');
        const employeesLookup = JSON.parse(document.getElementById('employeesLookupJson').textContent.trim() || '[]');
        const container = document.getElementById('hotTableContainer');

        const projectNames = projectsLookup.map(p => p.name);
        const employeeNames = employeesLookup.map(e => e.name);

        const subProjectNames = [];
        projectsLookup.forEach(p => {
            if (p.subProjects) {
                p.subProjects.forEach(s => {
                    if (!subProjectNames.includes(s.name)) subProjectNames.push(s.name);
                });
            }
        });

        // Transform raw data into handsontable rows
        const data = rawData.map(item => [
            item.id || '',
            item.date || new Date().toISOString().split('T')[0],
            item.employeeName || (employeeNames[0] || ''),
            item.projectName || '',
            item.subProjectName || '',
            item.activity || '',
            item.deliverable || '',
            item.duration !== undefined && item.duration !== null ? item.duration : '',
            item.progress !== undefined && item.progress !== null ? item.progress : '',
            item.status || '',
            item.notes || ''
        ]);

        // Ensure at least 5 empty rows if empty
        if (data.length === 0) {
            const defaultEmp = employeesLookup.find(e => e.id === currentUserId)?.name || employeeNames[0] || '';
            const today = new Date().toISOString().split('T')[0];
            for (let i = 0; i < 5; i++) {
                data.push(['', today, defaultEmp, '', '', '', '', '', '', '', '']);
            }
        }

        hotInstance = new Handsontable(container, {
            data: data,
            colHeaders: [
                'ID',
                'Tanggal',
                'Employee',
                'Project Utama',
                'Sub Project',
                'Aktivitas / Task',
                'Deliverable Output',
                'Durasi',
                'Performance (Progress)',
                'Status',
                'Catatan'
            ],
            columns: [{
                    readOnly: true,
                    width: 45
                }, // 0: ID
                {
                    type: 'date',
                    dateFormat: 'YYYY-MM-DD',
                    correctFormat: true,
                    width: 110,
                    renderer: dateCellRenderer
                }, // 1: Date
                {
                    readOnly: true,
                    width: 140
                }, // 2: Employee (read-only)
                {
                    type: 'autocomplete',
                    source: projectNames,
                    strict: false,
                    filter: false,
                    visibleRows: 8,
                    trimDropdown: false,
                    width: 280
                }, // 3: Project
                {
                    type: 'autocomplete',
                    source: subProjectNames,
                    strict: false,
                    filter: false,
                    visibleRows: 8,
                    trimDropdown: false,
                    width: 190
                }, // 4: Sub Project
                {
                    type: 'text',
                    width: 240
                }, // 5: Activity
                {
                    type: 'text',
                    width: 160
                }, // 6: Deliverable
                {
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0.0'
                    },
                    width: 95,
                    renderer: durationBadgeRenderer
                }, // 7: Duration
                {
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0'
                    },
                    width: 160,
                    renderer: progressBarRenderer
                }, // 8: Progress / Performance
                {
                    type: 'dropdown',
                    source: ['Completed', 'In Progress', 'Pending', 'Issue'],
                    strict: true,
                    filter: false,
                    visibleRows: 4,
                    trimDropdown: false,
                    width: 140,
                    renderer: statusBadgeRenderer
                }, // 9: Status
                {
                    type: 'text',
                    width: 140
                } // 10: Notes
            ],
            hiddenColumns: {
                columns: [0], // Hide ID column visually
                indicators: false
            },
            rowHeaders: true,
            rowHeights: 38,
            height: 540,
            width: '100%',
            stretchH: 'all',
            manualRowResize: true,
            manualColumnResize: true,
            columnSorting: true,
            contextMenu: true,
            autoWrapRow: true,
            autoWrapCol: true,
            licenseKey: 'non-commercial-and-evaluation',
            afterChange: function(changes, source) {
                if (!changes || source === 'loadData') return;
                changes.forEach(([row, prop, oldVal, newVal]) => {
                    // If Project column changed (column index 3)
                    if (prop === 3 && newVal !== oldVal) {
                        const matchedProj = projectsLookup.find(p => p.name === newVal);
                        if (matchedProj && matchedProj.subProjects && matchedProj.subProjects.length > 0) {
                            hotInstance.setDataAtCell(row, 4, matchedProj.subProjects[0].name);
                        } else {
                            hotInstance.setDataAtCell(row, 4, '');
                        }
                    }
                });
                updateRowCount();
            }
        });

        function updateRowCount() {
            if (!hotInstance) return;
            const count = hotInstance.countRows();
            const label = document.getElementById('rowCountLabel');
            if (label) {
                label.textContent = `${count} baris data termuat`;
            }
        }

        updateRowCount();
    });

    function addNewRow() {
        if (!hotInstance) return;
        const employeesLookup = JSON.parse(document.getElementById('employeesLookupJson').textContent.trim() || '[]');
        const today = new Date().toISOString().split('T')[0];
        const defaultEmp = employeesLookup.find(e => e.id === currentUserId)?.name || employeesLookup[0]?.name || '';

        hotInstance.alter('insert_row_below', hotInstance.countRows() - 1, 1);
        const newRowIdx = hotInstance.countRows() - 1;
        hotInstance.setDataAtCell(newRowIdx, 0, '');
        hotInstance.setDataAtCell(newRowIdx, 1, today);
        hotInstance.setDataAtCell(newRowIdx, 2, defaultEmp);
        hotInstance.setDataAtCell(newRowIdx, 3, '');
        hotInstance.setDataAtCell(newRowIdx, 4, '');
        hotInstance.setDataAtCell(newRowIdx, 5, '');
        hotInstance.setDataAtCell(newRowIdx, 6, '');
        hotInstance.setDataAtCell(newRowIdx, 7, '');
        hotInstance.setDataAtCell(newRowIdx, 8, '');
        hotInstance.setDataAtCell(newRowIdx, 9, '');
        hotInstance.setDataAtCell(newRowIdx, 10, '');

        setTimeout(() => {
            hotInstance.selectCell(newRowIdx, 3);
            hotInstance.scrollViewportTo(newRowIdx, 3);
        }, 50);
    }

    function saveHandsontableData() {
        if (!hotInstance) return;
        const saveBtn = document.getElementById('saveBtn');
        const alertBox = document.getElementById('statusAlert');
        const projectsLookup = JSON.parse(document.getElementById('projectsLookupJson').textContent.trim() || '[]');
        const employeesLookup = JSON.parse(document.getElementById('employeesLookupJson').textContent.trim() || '[]');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        const rawRows = hotInstance.getData();
        const formattedRows = [];

        rawRows.forEach(row => {
            const id = row[0];
            const date = row[1];
            const empName = row[2];
            const projName = row[3];
            const subName = row[4];
            const activity = row[5];
            const deliverable = row[6];
            const duration = row[7];
            const progress = row[8];
            const status = row[9];
            const notes = row[10];

            if ((!activity || String(activity).trim() === '') && (!projName || String(projName).trim() === '')) return;

            const matchedEmp = employeesLookup.find(e => e.name === empName);
            const matchedProj = projectsLookup.find(p => p.name === projName);
            let subId = null;
            if (matchedProj && matchedProj.subProjects) {
                const matchedSub = matchedProj.subProjects.find(s => s.name === subName);
                if (matchedSub) subId = matchedSub.id;
            }

            formattedRows.push({
                id: id || null,
                date: date || new Date().toISOString().split('T')[0],
                employeeId: matchedEmp ? matchedEmp.id : currentUserId,
                projectId: matchedProj ? matchedProj.id : null,
                subProjectId: subId,
                activity: activity || '-',
                deliverable: deliverable || null,
                duration: duration !== undefined && duration !== null && duration !== '' ? parseFloat(duration) : 1.0,
                progress: progress !== undefined && progress !== null && progress !== '' ? parseFloat(progress) : 100.0,
                status: status || 'Completed',
                notes: notes || null
            });
        });

        if (formattedRows.length === 0) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';
            alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-amber-50 border border-amber-200 text-amber-800';
            alertBox.innerHTML = '<span><i class="fa-solid fa-triangle-exclamation mr-2"></i> Tidak ada data baris yang diisi untuk disimpan.</span>';
            alertBox.classList.remove('hidden');
            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 3000);
            return;
        }

        fetch("{{ route('reports.daily-tasks.batch-save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rows: formattedRows
                })
            })
            .then(res => res.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';

                alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800';
                alertBox.innerHTML = `<span><i class="fa-solid fa-circle-check mr-2"></i> ${data.message}</span>`;
                alertBox.classList.remove('hidden');

                setTimeout(() => {
                    alertBox.classList.add('hidden');
                    window.location.reload();
                }, 1200);
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';

                alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-rose-50 border border-rose-200 text-rose-800';
                alertBox.innerHTML = `<span><i class="fa-solid fa-circle-exclamation mr-2"></i> Gagal menyimpan data: ${err.message}</span>`;
                alertBox.classList.remove('hidden');
            });
    }
</script>
@endpush
@endsection