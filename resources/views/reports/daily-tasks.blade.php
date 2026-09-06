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
            <p class="text-xs text-gray-500 m-0">Input aktivitas harian, pilih tipe project, project utama, sub project, stage, serta lampirkan bukti pekerjaan (gambar/PDF/video).</p>
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
        <form method="GET" action="{{ route('reports.daily-tasks') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
            @if ($authUser && $authUser->isSuperadmin())
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Employee</label>
                <select name="employee" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua User</option>
                    @foreach ($employees as $emp)
                    <option value="{{ $emp->intUser_ID }}" {{ request('employee') == $emp->intUser_ID ? 'selected' : '' }}>
                        {{ $emp->txtEmployeeName }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Tipe Project</label>
                <select name="project_type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Tipe Project</option>
                    @foreach ($projectTypes as $pt)
                    <option value="{{ $pt->intProjectType_ID }}" {{ request('project_type') == $pt->intProjectType_ID ? 'selected' : '' }}>
                        {{ $pt->txtProjectTypeName }}
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
                        {{ $prj->txtProjectName }} {{ $prj->intUser_ID === $authUserId ? '★' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pilih Sub Project</label>
                <select name="sub_project" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Sub Project ({{ count($subProjects) }})</option>
                    @foreach ($subProjects as $sp)
                    <option value="{{ $sp->intSubProject_ID }}" {{ request('sub_project') == $sp->intSubProject_ID ? 'selected' : '' }}>
                        {{ $sp->txtSubProjectName }}
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

            @if(request()->hasAny(['employee', 'project_type', 'project', 'sub_project', 'start_date', 'end_date']))
            <div class="lg:col-span-6 flex justify-end">
                <a href="{{ route('reports.daily-tasks') }}" class="text-xs text-rose-600 hover:text-rose-800 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Reset Semua Filter
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Status Alert Bar -->
    <div id="statusAlert" class="hidden p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs transition-all"></div>

    <!-- Handsontable Grid Shell -->
    <div class="handsontable-container-card p-4 bg-white rounded-2xl border border-[#DDE5DD] shadow-xs">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                    <i class="fa-solid fa-table-cells mr-1.5 text-[#006838]"></i> Interactive Data Grid
                </span>
                <span class="text-xs text-gray-400 font-medium" id="rowCountLabel">Memuat baris...</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <span>Alur: <strong class="text-gray-700">Tipe Project &rarr; Project Utama &rarr; Sub Project &rarr; Stage</strong></span>
            </div>
        </div>

        <div id="hotTableContainer" class="w-full bg-white" style="height: 560px; min-height: 480px;"></div>
    </div>

    <!-- Help & Shortcuts note -->
    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 text-xs text-gray-600 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-lightbulb text-emerald-600 text-sm"></i>
            <span><strong>Tips:</strong> Pilih <strong>Tipe Project</strong> dahulu untuk menyaring Project Utama. Kolom <strong>Stage</strong> terhubung otomatis dengan tahapan sub project atau direct stage project. Klik tombol <strong>Upload</strong> pada kolom Lampiran untuk melampirkan gambar, PDF, atau video.</span>
        </div>
        <span class="font-mono text-[10px] text-gray-400 shrink-0">Handsontable v14 Modern</span>
    </div>

</div>

<!-- Modal: View / Preview Attachment -->
<div id="attachmentViewModal" class="hidden fixed inset-0 z-[99999] bg-black/75 backdrop-blur-xs flex items-center justify-center p-4" style="z-index: 99999;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden border border-gray-200">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
            <div class="flex items-center gap-3">
                <span id="viewModalIcon" class="w-9 h-9 rounded-xl flex items-center justify-center text-base bg-emerald-100 text-[#006838]">
                    <i class="fa-solid fa-paperclip"></i>
                </span>
                <div>
                    <h3 id="viewModalTitle" class="text-sm font-black text-gray-900 m-0 truncate max-w-md">Pratinjau Lampiran</h3>
                    <p id="viewModalSubtitle" class="text-[11px] text-gray-500 m-0"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="viewModalExternalBtn" href="#" target="_blank" class="px-3 py-1.5 rounded-xl bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold border border-gray-200 transition flex items-center gap-1.5 no-underline" title="Buka di tab baru">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                    <span class="hidden sm:inline">Tab Baru</span>
                </a>
                <a id="viewModalDownloadBtn" href="#" download class="px-3 py-1.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white text-xs font-bold transition flex items-center gap-1.5 no-underline" title="Download berkas">
                    <i class="fa-solid fa-download text-[11px]"></i>
                    <span class="hidden sm:inline">Download</span>
                </a>
                <button type="button" onclick="changeAttachmentFromModal()" class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 transition flex items-center gap-1.5 cursor-pointer" title="Ganti berkas lampiran">
                    <i class="fa-solid fa-arrow-up-from-bracket text-[11px] text-amber-600"></i>
                    <span class="hidden sm:inline">Ganti Berkas</span>
                </button>
                <button type="button" onclick="deleteAttachmentFromModal()" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200 transition flex items-center gap-1.5 cursor-pointer" title="Hapus berkas lampiran">
                    <i class="fa-solid fa-trash-can text-[11px] text-rose-600"></i>
                    <span class="hidden sm:inline">Hapus Berkas</span>
                </button>
                <button type="button" onclick="closeAttachmentViewModal()" class="w-8 h-8 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-600 flex items-center justify-center transition cursor-pointer" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body Content -->
        <div class="p-4 flex-1 overflow-y-auto bg-gray-900/5 flex items-center justify-center min-h-[360px]">
            <!-- Image View -->
            <div id="viewModalImageContainer" class="hidden w-full flex justify-center items-center">
                <img id="viewModalImage" src="" alt="Pratinjau Gambar" class="max-h-[72vh] max-w-full rounded-xl object-contain shadow-md border border-gray-200 bg-white">
            </div>

            <!-- PDF View -->
            <div id="viewModalPdfContainer" class="hidden w-full h-[72vh]">
                <iframe id="viewModalPdfFrame" src="" class="w-full h-full rounded-xl border border-gray-300 bg-white"></iframe>
            </div>

            <!-- Video View -->
            <div id="viewModalVideoContainer" class="hidden w-full flex justify-center items-center">
                <video id="viewModalVideoPlayer" controls autoplay class="max-h-[72vh] w-full max-w-3xl rounded-xl shadow-lg bg-black"></video>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Upload Attachment -->
<div id="attachmentUploadModal" class="hidden fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" style="z-index: 99999;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-200">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm bg-emerald-100 text-[#006838]">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </span>
                <div>
                    <h3 class="text-sm font-black text-gray-900 m-0">Upload Lampiran Daily Task</h3>
                    <p id="uploadModalRowContext" class="text-[11px] text-gray-500 m-0">Pilih berkas bukti pekerjaan</p>
                </div>
            </div>
            <button type="button" onclick="closeAttachmentUploadModal()" class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-600 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <!-- Modal Body / Form -->
        <form id="attachmentUploadForm" onsubmit="submitAttachmentUpload(event)" class="p-5 space-y-4">
            <input type="hidden" id="uploadTaskId" value="">
            <input type="hidden" id="uploadRowIdx" value="">

            <div id="dropzoneContainer" onclick="document.getElementById('attachmentFileInput').click()" class="border-2 border-dashed border-emerald-300 hover:border-[#006838] bg-emerald-50/25 hover:bg-emerald-50/55 rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#006838] group-hover:scale-110 flex items-center justify-center text-xl mb-2.5 transition-transform shadow-xs">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <p class="text-xs font-bold text-gray-800 m-0">Klik atau seret file ke sini</p>
                <p class="text-[11px] text-gray-500 mt-1 mb-0">Mendukung: <strong>Gambar (JPG, PNG, GIF, WEBP)</strong>, <strong>PDF</strong>, dan <strong>Video (MP4, WEBM)</strong></p>
                <p class="text-[10px] text-gray-400 mt-0.5 mb-0">Maksimum ukuran: 50 MB</p>
                <input type="file" id="attachmentFileInput" accept="image/*,.pdf,video/*" class="hidden" onchange="handleFileSelection(event)">
            </div>

            <!-- File Selected Preview Card -->
            <div id="fileSelectedCard" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <span id="fileSelectedIcon" class="text-lg text-emerald-700 shrink-0">
                        <i class="fa-solid fa-file"></i>
                    </span>
                    <div class="truncate">
                        <p id="fileSelectedName" class="text-xs font-bold text-gray-900 m-0 truncate">nama-file.ext</p>
                        <p id="fileSelectedSize" class="text-[10px] text-gray-500 m-0">0 KB</p>
                    </div>
                </div>
                <button type="button" onclick="clearSelectedFile()" class="text-gray-400 hover:text-rose-600 p-1 cursor-pointer">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            </div>

            <!-- Alert in Upload Modal -->
            <div id="uploadModalAlert" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAttachmentUploadModal()" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="submitUploadBtn" disabled class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Unggah Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JSON Payloads -->
<script type="application/json" id="initialHotData">
    @json($handsontableData)
</script>
<script type="application/json" id="projectTypesLookupJson">
    @json($projectTypesLookup)
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
    let currentViewingRowIdx = null;
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

    // Custom Performance / Progress Bar Renderer
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

        let barColor = '#006838';
        if (val < 40) {
            barColor = '#ef4444';
        } else if (val < 75) {
            barColor = '#3b82f6';
        } else if (val < 100) {
            barColor = '#10b981';
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

    // Custom Lampiran / Attachment Cell Renderer
    function attachmentCellRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.BaseRenderer.apply(this, arguments);
        td.className = (td.className || '').replace(/htTop|htBottom/g, '') + ' htCenter htMiddle ht-attachment-cell';
        td.style.setProperty('vertical-align', 'middle', 'important');
        td.style.setProperty('text-align', 'center', 'important');
        td.style.setProperty('padding', '0px', 'important');

        let attachment = null;
        if (value) {
            if (typeof value === 'object') {
                attachment = value;
            } else if (typeof value === 'string' && value.startsWith('{')) {
                try {
                    attachment = JSON.parse(value);
                } catch (e) {}
            }
        }

        if (attachment && (attachment.path || attachment.url)) {
            const type = (attachment.type || 'image').toLowerCase();
            const name = attachment.name || 'Berkas';
            let iconClass = 'fa-solid fa-image text-sm';
            let btnStyle = 'background-color: #eff6ff; color: #2563eb; border-color: #bfdbfe;';
            let typeLabel = 'Gambar';

            if (type === 'pdf') {
                iconClass = 'fa-solid fa-file-pdf text-sm';
                btnStyle = 'background-color: #fef2f2; color: #dc2626; border-color: #fecaca;';
                typeLabel = 'PDF';
            } else if (type === 'video') {
                iconClass = 'fa-solid fa-file-video text-sm';
                btnStyle = 'background-color: #faf5ff; color: #9333ea; border-color: #e9d5ff;';
                typeLabel = 'Video';
            }

            const escapedName = (name || 'Berkas').replace(/"/g, '&quot;');
            td.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 38px; min-height: 38px; margin: 0; padding: 0;" onclick="event.stopPropagation()">
                    <button type="button" onclick="previewAttachment(${row})" class="w-8 h-8 rounded-xl flex items-center justify-center transition-all shadow-2xs hover:shadow-xs cursor-pointer hover:scale-110 active:scale-95" style="border-width: 1px; border-style: solid; ${btnStyle}" title="Lihat ${typeLabel} (${escapedName})">
                        <i class="${iconClass}"></i>
                    </button>
                </div>
            `;
        } else {
            td.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 38px; min-height: 38px; margin: 0; padding: 0;" onclick="event.stopPropagation()">
                    <button type="button" onclick="openUploadModal(${row})" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 hover:bg-emerald-100 text-[#006838] hover:text-[#004d29] text-[11px] font-bold border border-emerald-200/90 shadow-2xs hover:shadow-xs transition-all cursor-pointer" style="line-height: 1;">
                        <i class="fa-solid fa-cloud-arrow-up text-[11px] text-[#006838]"></i>
                        <span>Upload</span>
                    </button>
                </div>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const rawData = JSON.parse(document.getElementById('initialHotData').textContent.trim() || '[]');
        const projectTypesLookup = JSON.parse(document.getElementById('projectTypesLookupJson').textContent.trim() || '[]');
        const projectsLookup = JSON.parse(document.getElementById('projectsLookupJson').textContent.trim() || '[]');
        const employeesLookup = JSON.parse(document.getElementById('employeesLookupJson').textContent.trim() || '[]');
        const container = document.getElementById('hotTableContainer');

        // Extract project types only from existing projects in projectsLookup
        const projectTypeNames = [];
        projectsLookup.forEach(p => {
            if (p.projectTypeName && !projectTypeNames.includes(p.projectTypeName)) {
                projectTypeNames.push(p.projectTypeName);
            }
        });
        if (projectTypeNames.length === 0) {
            projectTypesLookup.forEach(t => {
                if (t.name && !projectTypeNames.includes(t.name)) projectTypeNames.push(t.name);
            });
        }
        const projectNames = projectsLookup.map(p => p.name);
        const employeeNames = employeesLookup.map(e => e.name);

        const subProjectNames = [];
        const allStageSteps = [];
        projectsLookup.forEach(p => {
            if (p.directStages) {
                p.directStages.forEach(s => {
                    if (!allStageSteps.includes(s.step)) allStageSteps.push(s.step);
                });
            }
            if (p.subProjects) {
                p.subProjects.forEach(s => {
                    if (!subProjectNames.includes(s.name)) subProjectNames.push(s.name);
                    if (s.stages) {
                        s.stages.forEach(st => {
                            if (!allStageSteps.includes(st.step)) allStageSteps.push(st.step);
                        });
                    }
                });
            }
        });

        // Transform raw data into handsontable rows
        const data = rawData.map(item => [
            item.id || '',
            item.date || new Date().toISOString().split('T')[0],
            item.employeeName || (employeeNames[0] || ''),
            item.projectTypeName || '',
            item.projectName || '',
            item.subProjectName || '',
            item.stageStep || '',
            item.activity || '',
            item.deliverable || '',
            item.duration !== undefined && item.duration !== null ? item.duration : '',
            item.progress !== undefined && item.progress !== null ? item.progress : '',
            item.status || '',
            item.notes || '',
            item.attachment || null
        ]);

        // Ensure at least 5 empty rows if empty
        if (data.length === 0) {
            const defaultEmp = employeesLookup.find(e => e.id === currentUserId)?.name || employeeNames[0] || '';
            const today = new Date().toISOString().split('T')[0];
            for (let i = 0; i < 5; i++) {
                data.push(['', today, defaultEmp, '', '', '', '', '', '', '', '', 'Completed', '', null]);
            }
        }

        hotInstance = new Handsontable(container, {
            data: data,
            colHeaders: [
                'ID',
                'Tanggal',
                'Employee',
                'Tipe Project',
                'Project Utama',
                'Sub Project',
                'Stage',
                'Aktivitas / Task',
                'Deliverable Output',
                'Durasi',
                'Performance (Progress)',
                'Status',
                'Catatan',
                'Lampiran'
            ],
            columns: [{
                    readOnly: true,
                    width: 45
                }, // 0: ID
                {
                    type: 'date',
                    dateFormat: 'YYYY-MM-DD',
                    correctFormat: true,
                    width: 105,
                    renderer: dateCellRenderer
                }, // 1: Date
                {
                    readOnly: true,
                    width: 135
                }, // 2: Employee (read-only)
                {
                    type: 'autocomplete',
                    source: projectTypeNames,
                    strict: false,
                    filter: false,
                    visibleRows: 6,
                    trimDropdown: false,
                    width: 160
                }, // 3: Tipe Project
                {
                    type: 'autocomplete',
                    source: projectNames,
                    strict: false,
                    filter: false,
                    visibleRows: 8,
                    trimDropdown: false,
                    width: 250
                }, // 4: Project Utama
                {
                    type: 'autocomplete',
                    source: subProjectNames,
                    strict: false,
                    filter: false,
                    visibleRows: 8,
                    trimDropdown: false,
                    width: 180
                }, // 5: Sub Project
                {
                    type: 'autocomplete',
                    source: allStageSteps,
                    strict: false,
                    filter: false,
                    visibleRows: 8,
                    trimDropdown: false,
                    width: 160
                }, // 6: Stage
                {
                    type: 'text',
                    width: 230
                }, // 7: Activity
                {
                    type: 'text',
                    width: 150
                }, // 8: Deliverable
                {
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0.0'
                    },
                    width: 90,
                    renderer: durationBadgeRenderer
                }, // 9: Duration
                {
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0'
                    },
                    width: 150,
                    renderer: progressBarRenderer
                }, // 10: Progress / Performance
                {
                    type: 'dropdown',
                    source: ['Completed', 'In Progress', 'Pending', 'Issue'],
                    strict: true,
                    filter: false,
                    visibleRows: 4,
                    trimDropdown: false,
                    width: 130,
                    renderer: statusBadgeRenderer
                }, // 11: Status
                {
                    type: 'text',
                    width: 130
                }, // 12: Notes
                {
                    readOnly: true,
                    width: 110,
                    renderer: attachmentCellRenderer
                } // 13: Lampiran
            ],
            hiddenColumns: {
                columns: [0], // Hide ID column visually
                indicators: false
            },
            rowHeaders: true,
            rowHeights: 38,
            height: 560,
            width: '100%',
            stretchH: 'all',
            manualRowResize: true,
            manualColumnResize: true,
            columnSorting: true,
            contextMenu: true,
            autoWrapRow: true,
            autoWrapCol: true,
            licenseKey: 'non-commercial-and-evaluation',
            cells: function(row, col, prop) {
                const cellProperties = {};

                // Dynamic cascading options for Tipe Project (Col 3)
                if (col === 3) {
                    const empName = this.instance ? this.instance.getDataAtCell(row, 2) : null;
                    let availableTypes = [];
                    if (empName) {
                        const empProjects = projectsLookup.filter(p =>
                            p.userName === empName ||
                            (p.associatedUserNames && Array.isArray(p.associatedUserNames) && p.associatedUserNames.includes(empName))
                        );
                        if (empProjects.length > 0) {
                            empProjects.forEach(p => {
                                if (p.projectTypeName && !availableTypes.includes(p.projectTypeName)) {
                                    availableTypes.push(p.projectTypeName);
                                }
                            });
                        }
                    }

                    if (availableTypes.length === 0) {
                        availableTypes = projectTypeNames;
                    }

                    cellProperties.source = availableTypes;
                }

                // Dynamic cascading options for Project Utama (Col 4)
                if (col === 4) {
                    const empName = this.instance ? this.instance.getDataAtCell(row, 2) : null;
                    const typeName = this.instance ? this.instance.getDataAtCell(row, 3) : null;
                    let filteredProjects = projectsLookup;
                    if (empName) {
                        const empProj = projectsLookup.filter(p =>
                            p.userName === empName ||
                            (p.associatedUserNames && Array.isArray(p.associatedUserNames) && p.associatedUserNames.includes(empName))
                        );
                        if (empProj.length > 0) filteredProjects = empProj;
                    }
                    if (typeName) {
                        const matched = filteredProjects.filter(p => p.projectTypeName === typeName);
                        cellProperties.source = matched.length > 0 ? matched.map(p => p.name) : filteredProjects.map(p => p.name);
                    } else {
                        cellProperties.source = filteredProjects.map(p => p.name);
                    }
                }

                // Dynamic cascading options for Sub Project (Col 5)
                if (col === 5) {
                    const projName = this.instance ? this.instance.getDataAtCell(row, 4) : null;
                    if (projName) {
                        const p = projectsLookup.find(x => x.name === projName);
                        if (p && p.hasSubProjects && p.subProjects && p.subProjects.length > 0) {
                            cellProperties.source = p.subProjects.map(s => s.name);
                            cellProperties.readOnly = false;
                        } else {
                            cellProperties.source = [];
                            cellProperties.readOnly = true;
                        }
                    } else {
                        cellProperties.source = subProjectNames;
                    }
                }

                // Dynamic cascading options for Stage (Col 6)
                if (col === 6) {
                    const projName = this.instance ? this.instance.getDataAtCell(row, 4) : null;
                    const subName = this.instance ? this.instance.getDataAtCell(row, 5) : null;
                    let stagesList = [];
                    if (projName) {
                        const p = projectsLookup.find(x => x.name === projName);
                        if (p) {
                            if (subName && p.subProjects) {
                                const sp = p.subProjects.find(s => s.name === subName);
                                if (sp && sp.stages && sp.stages.length > 0) {
                                    stagesList = sp.stages.map(st => st.step);
                                }
                            }
                            if (stagesList.length === 0 && p.directStages && p.directStages.length > 0) {
                                stagesList = p.directStages.map(st => st.step);
                            }
                        }
                    }
                    cellProperties.source = stagesList.length > 0 ? stagesList : allStageSteps;
                }

                return cellProperties;
            },
            afterChange: function(changes, source) {
                if (!changes || source === 'loadData') return;
                changes.forEach(([row, prop, oldVal, newVal]) => {
                    // Col 2: Employee changed
                    if (prop === 2 && newVal !== oldVal) {
                        hotInstance.setDataAtCell(row, 3, '');
                        hotInstance.setDataAtCell(row, 4, '');
                        hotInstance.setDataAtCell(row, 5, '');
                        hotInstance.setDataAtCell(row, 6, '');
                    }

                    // Col 3: Tipe Project changed
                    if (prop === 3 && newVal !== oldVal) {
                        const curProj = hotInstance.getDataAtCell(row, 4);
                        if (curProj) {
                            const p = projectsLookup.find(x => x.name === curProj);
                            if (p && p.projectTypeName && p.projectTypeName !== newVal) {
                                hotInstance.setDataAtCell(row, 4, '');
                                hotInstance.setDataAtCell(row, 5, '');
                                hotInstance.setDataAtCell(row, 6, '');
                            }
                        }
                    }

                    // Col 4: Project Utama changed
                    if (prop === 4 && newVal !== oldVal) {
                        const p = projectsLookup.find(x => x.name === newVal);
                        if (p) {
                            // Auto fill Tipe Project if empty or different
                            if (p.projectTypeName && hotInstance.getDataAtCell(row, 3) !== p.projectTypeName) {
                                hotInstance.setDataAtCell(row, 3, p.projectTypeName);
                            }
                            if (p.hasSubProjects && p.subProjects && p.subProjects.length > 0) {
                                hotInstance.setDataAtCell(row, 5, p.subProjects[0].name);
                                if (p.subProjects[0].stages && p.subProjects[0].stages.length > 0) {
                                    hotInstance.setDataAtCell(row, 6, p.subProjects[0].stages[0].step);
                                } else {
                                    hotInstance.setDataAtCell(row, 6, '');
                                }
                            } else {
                                hotInstance.setDataAtCell(row, 5, '');
                                if (p.directStages && p.directStages.length > 0) {
                                    hotInstance.setDataAtCell(row, 6, p.directStages[0].step);
                                } else {
                                    hotInstance.setDataAtCell(row, 6, '');
                                }
                            }
                        }
                    }

                    // Col 5: Sub Project changed
                    if (prop === 5 && newVal !== oldVal) {
                        const projName = hotInstance.getDataAtCell(row, 4);
                        const p = projectsLookup.find(x => x.name === projName);
                        if (p && p.subProjects) {
                            const sp = p.subProjects.find(s => s.name === newVal);
                            if (sp && sp.stages && sp.stages.length > 0) {
                                hotInstance.setDataAtCell(row, 6, sp.stages[0].step);
                            } else {
                                hotInstance.setDataAtCell(row, 6, '');
                            }
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
        hotInstance.setDataAtCell(newRowIdx, 11, 'Completed');
        hotInstance.setDataAtCell(newRowIdx, 12, '');
        hotInstance.setDataAtCell(newRowIdx, 13, null);

        setTimeout(() => {
            hotInstance.selectCell(newRowIdx, 3);
            hotInstance.scrollViewportTo(newRowIdx, 3);
        }, 50);
    }

    function saveHandsontableData(callbackAfterSave) {
        if (!hotInstance) return;
        const saveBtn = document.getElementById('saveBtn');
        const alertBox = document.getElementById('statusAlert');
        const projectTypesLookup = JSON.parse(document.getElementById('projectTypesLookupJson').textContent.trim() || '[]');
        const projectsLookup = JSON.parse(document.getElementById('projectsLookupJson').textContent.trim() || '[]');
        const employeesLookup = JSON.parse(document.getElementById('employeesLookupJson').textContent.trim() || '[]');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        const rawRows = hotInstance.getData();
        const formattedRows = [];

        rawRows.forEach((row, clientIndex) => {
            const id = row[0];
            const date = row[1];
            const empName = row[2];
            const typeName = row[3];
            const projName = row[4];
            const subName = row[5];
            const stageName = row[6];
            const activity = row[7];
            const deliverable = row[8];
            const duration = row[9];
            const progress = row[10];
            const status = row[11];
            const notes = row[12];

            if ((!activity || String(activity).trim() === '') && (!projName || String(projName).trim() === '')) return;

            const matchedEmp = employeesLookup.find(e => e.name === empName);
            const matchedType = projectTypesLookup.find(t => t.name === typeName);
            const matchedProj = projectsLookup.find(p => p.name === projName);

            let typeId = matchedType ? matchedType.id : null;
            if (!typeId && matchedProj && matchedProj.projectTypeId) {
                typeId = matchedProj.projectTypeId;
            }

            let subId = null;
            let stageId = null;

            if (matchedProj) {
                if (matchedProj.subProjects) {
                    const matchedSub = matchedProj.subProjects.find(s => s.name === subName);
                    if (matchedSub) {
                        subId = matchedSub.id;
                        if (matchedSub.stages) {
                            const matchedStage = matchedSub.stages.find(st => st.step === stageName);
                            if (matchedStage) stageId = matchedStage.id;
                        }
                    }
                }
                if (!stageId && matchedProj.directStages) {
                    const matchedStage = matchedProj.directStages.find(st => st.step === stageName);
                    if (matchedStage) stageId = matchedStage.id;
                }
            }

            formattedRows.push({
                id: id || null,
                date: date || new Date().toISOString().split('T')[0],
                employeeId: matchedEmp ? matchedEmp.id : currentUserId,
                projectTypeId: typeId,
                projectId: matchedProj ? matchedProj.id : null,
                subProjectId: subId,
                stageId: stageId,
                activity: activity || '-',
                deliverable: deliverable || null,
                duration: duration !== undefined && duration !== null && duration !== '' ? parseFloat(duration) : 1.0,
                progress: progress !== undefined && progress !== null && progress !== '' ? parseFloat(progress) : 100.0,
                status: status || 'Completed',
                notes: notes || null,
                _clientIndex: clientIndex
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

                // Update assigned task IDs to handsontable rows without needing reload
                if (data.tasks && Array.isArray(data.tasks)) {
                    data.tasks.forEach(t => {
                        const originalRow = formattedRows[t.clientIndex];
                        if (originalRow && originalRow._clientIndex !== undefined && t.id) {
                            hotInstance.setDataAtCell(originalRow._clientIndex, 0, t.id);
                        }
                    });
                }

                alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800';
                alertBox.innerHTML = `<span><i class="fa-solid fa-circle-check mr-2"></i> ${data.message}</span>`;
                alertBox.classList.remove('hidden');

                if (typeof callbackAfterSave === 'function') {
                    callbackAfterSave(data);
                } else {
                    setTimeout(() => {
                        alertBox.classList.add('hidden');
                    }, 4000);
                }
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';

                alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-rose-50 border border-rose-200 text-rose-800';
                alertBox.innerHTML = `<span><i class="fa-solid fa-circle-exclamation mr-2"></i> Gagal menyimpan data: ${err.message}</span>`;
                alertBox.classList.remove('hidden');
            });
    }

    // --- Lampiran / Attachment Preview Functions ---
    function previewAttachment(rowIdx) {
        if (!hotInstance) return;
        currentViewingRowIdx = rowIdx;
        const attachment = hotInstance.getDataAtCell(rowIdx, 13);
        if (!attachment || (!attachment.url && !attachment.path)) return;

        const modal = document.getElementById('attachmentViewModal');
        const titleEl = document.getElementById('viewModalTitle');
        const subtitleEl = document.getElementById('viewModalSubtitle');
        const iconEl = document.getElementById('viewModalIcon');
        const extBtn = document.getElementById('viewModalExternalBtn');
        const dlBtn = document.getElementById('viewModalDownloadBtn');

        const imgContainer = document.getElementById('viewModalImageContainer');
        const imgEl = document.getElementById('viewModalImage');
        const pdfContainer = document.getElementById('viewModalPdfContainer');
        const pdfFrame = document.getElementById('viewModalPdfFrame');
        const videoContainer = document.getElementById('viewModalVideoContainer');
        const videoEl = document.getElementById('viewModalVideoPlayer');

        // Hide all containers
        imgContainer.classList.add('hidden');
        pdfContainer.classList.add('hidden');
        videoContainer.classList.add('hidden');
        videoEl.pause();

        const fileUrl = attachment.url;
        const fileName = attachment.name || 'Berkas Lampiran';
        const fileType = (attachment.type || 'image').toLowerCase();

        titleEl.textContent = fileName;
        subtitleEl.textContent = `Tipe: ${fileType.toUpperCase()}`;
        extBtn.href = fileUrl;
        dlBtn.href = fileUrl;
        dlBtn.setAttribute('download', fileName);

        if (fileType === 'image') {
            iconEl.className = 'w-9 h-9 rounded-xl flex items-center justify-center text-base bg-blue-100 text-blue-700';
            iconEl.innerHTML = '<i class="fa-solid fa-image"></i>';
            imgEl.src = fileUrl;
            imgContainer.classList.remove('hidden');
        } else if (fileType === 'pdf') {
            iconEl.className = 'w-9 h-9 rounded-xl flex items-center justify-center text-base bg-rose-100 text-rose-700';
            iconEl.innerHTML = '<i class="fa-solid fa-file-pdf"></i>';
            pdfFrame.src = fileUrl;
            pdfContainer.classList.remove('hidden');
        } else if (fileType === 'video') {
            iconEl.className = 'w-9 h-9 rounded-xl flex items-center justify-center text-base bg-purple-100 text-purple-700';
            iconEl.innerHTML = '<i class="fa-solid fa-file-video"></i>';
            videoEl.src = fileUrl;
            videoContainer.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
    }

    function closeAttachmentViewModal() {
        currentViewingRowIdx = null;
        const modal = document.getElementById('attachmentViewModal');
        const videoEl = document.getElementById('viewModalVideoPlayer');
        if (videoEl) videoEl.pause();
        const pdfFrame = document.getElementById('viewModalPdfFrame');
        if (pdfFrame) pdfFrame.src = '';
        modal.classList.add('hidden');
    }

    function changeAttachmentFromModal() {
        if (currentViewingRowIdx === null) return;
        const targetRow = currentViewingRowIdx;
        closeAttachmentViewModal();
        openUploadModal(targetRow);
    }

    function deleteAttachmentFromModal() {
        if (currentViewingRowIdx === null) return;
        const targetRow = currentViewingRowIdx;
        deleteAttachmentPrompt(targetRow, () => {
            closeAttachmentViewModal();
        });
    }

    // --- Lampiran / Attachment Upload Functions ---
    function openUploadModal(rowIdx) {
        if (!hotInstance) return;
        const taskId = hotInstance.getDataAtCell(rowIdx, 0);

        if (!taskId) {
            // Task has not been saved to database yet
            const activity = hotInstance.getDataAtCell(rowIdx, 7);
            const project = hotInstance.getDataAtCell(rowIdx, 4);

            if ((!activity || !String(activity).trim()) && (!project || !String(project).trim())) {
                alert('Silakan lengkapi baris aktivitas atau project terlebih dahulu sebelum mengunggah lampiran.');
                return;
            }

            // Inform user and auto-save first
            const alertBox = document.getElementById('statusAlert');
            alertBox.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800';
            alertBox.innerHTML = '<span><i class="fa-solid fa-spinner fa-spin mr-2"></i> Menyimpan baris aktivitas ke server sebelum upload berkas...</span>';
            alertBox.classList.remove('hidden');

            saveHandsontableData(() => {
                const newTaskId = hotInstance.getDataAtCell(rowIdx, 0);
                if (newTaskId) {
                    showUploadDialog(newTaskId, rowIdx);
                }
            });
            return;
        }

        showUploadDialog(taskId, rowIdx);
    }

    function showUploadDialog(taskId, rowIdx) {
        document.getElementById('uploadTaskId').value = taskId;
        document.getElementById('uploadRowIdx').value = rowIdx;

        const date = hotInstance.getDataAtCell(rowIdx, 1);
        const activity = hotInstance.getDataAtCell(rowIdx, 7) || 'Aktivitas';
        document.getElementById('uploadModalRowContext').textContent = `${date} • ${activity}`;

        clearSelectedFile();
        document.getElementById('uploadModalAlert').classList.add('hidden');
        document.getElementById('attachmentUploadModal').classList.remove('hidden');
    }

    function closeAttachmentUploadModal() {
        document.getElementById('attachmentUploadModal').classList.add('hidden');
        clearSelectedFile();
    }

    function handleFileSelection(event) {
        const file = event.target.files[0];
        if (!file) return;

        const submitBtn = document.getElementById('submitUploadBtn');
        const card = document.getElementById('fileSelectedCard');
        const nameEl = document.getElementById('fileSelectedName');
        const sizeEl = document.getElementById('fileSelectedSize');
        const iconEl = document.getElementById('fileSelectedIcon');
        const alertEl = document.getElementById('uploadModalAlert');

        alertEl.classList.add('hidden');

        // Check file size (max 50 MB)
        if (file.size > 50 * 1024 * 1024) {
            alertEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-800';
            alertEl.textContent = 'Ukuran berkas melebihi batas maksimum (50 MB).';
            alertEl.classList.remove('hidden');
            clearSelectedFile();
            return;
        }

        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB (' + file.type + ')';

        const ext = file.name.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext) || file.type.startsWith('image/')) {
            iconEl.innerHTML = '<i class="fa-solid fa-image text-blue-600"></i>';
        } else if (ext === 'pdf' || file.type.includes('pdf')) {
            iconEl.innerHTML = '<i class="fa-solid fa-file-pdf text-rose-600"></i>';
        } else if (['mp4', 'webm', 'ogg', 'mov'].includes(ext) || file.type.startsWith('video/')) {
            iconEl.innerHTML = '<i class="fa-solid fa-file-video text-purple-600"></i>';
        } else {
            iconEl.innerHTML = '<i class="fa-solid fa-paperclip text-emerald-600"></i>';
        }

        card.classList.remove('hidden');
        submitBtn.disabled = false;
    }

    function clearSelectedFile() {
        const input = document.getElementById('attachmentFileInput');
        if (input) input.value = '';
        document.getElementById('fileSelectedCard').classList.add('hidden');
        document.getElementById('submitUploadBtn').disabled = true;
    }

    function submitAttachmentUpload(event) {
        event.preventDefault();
        const taskId = document.getElementById('uploadTaskId').value;
        const rowIdx = parseInt(document.getElementById('uploadRowIdx').value);
        const fileInput = document.getElementById('attachmentFileInput');
        const submitBtn = document.getElementById('submitUploadBtn');
        const alertEl = document.getElementById('uploadModalAlert');

        if (!fileInput.files || !fileInput.files[0]) {
            alertEl.className = 'p-3 rounded-xl text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-800';
            alertEl.textContent = 'Silakan pilih file terlebih dahulu.';
            alertEl.classList.remove('hidden');
            return;
        }

        const formData = new FormData();
        formData.append('attachment', fileInput.files[0]);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengunggah...';

        const uploadUrl = `{{ url('reports/daily-tasks') }}/${taskId}/attachment`;

        fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> <span>Unggah Sekarang</span>';

                if (data.success) {
                    hotInstance.setDataAtCell(rowIdx, 13, data.attachment);
                    closeAttachmentUploadModal();

                    const statusAlert = document.getElementById('statusAlert');
                    statusAlert.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800';
                    statusAlert.innerHTML = `<span><i class="fa-solid fa-circle-check mr-2"></i> ${data.message}</span>`;
                    statusAlert.classList.remove('hidden');
                    setTimeout(() => statusAlert.classList.add('hidden'), 3500);
                } else {
                    alertEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-800';
                    alertEl.textContent = data.message || 'Gagal mengunggah berkas.';
                    alertEl.classList.remove('hidden');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> <span>Unggah Sekarang</span>';
                alertEl.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-800';
                alertEl.textContent = 'Terjadi kesalahan saat mengunggah: ' + err.message;
                alertEl.classList.remove('hidden');
            });
    }

    function deleteAttachmentPrompt(rowIdx, callbackOnSuccess) {
        if (!hotInstance) return;
        const taskId = hotInstance.getDataAtCell(rowIdx, 0);
        if (!taskId) return;

        if (!confirm('Apakah Anda yakin ingin menghapus lampiran pada baris ini?')) {
            return;
        }

        const deleteUrl = `{{ url('reports/daily-tasks') }}/${taskId}/attachment`;

        fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    hotInstance.setDataAtCell(rowIdx, 13, null);

                    if (typeof callbackOnSuccess === 'function') {
                        callbackOnSuccess();
                    }

                    const statusAlert = document.getElementById('statusAlert');
                    statusAlert.className = 'p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800';
                    statusAlert.innerHTML = `<span><i class="fa-solid fa-circle-check mr-2"></i> ${data.message}</span>`;
                    statusAlert.classList.remove('hidden');
                    setTimeout(() => statusAlert.classList.add('hidden'), 3000);
                } else {
                    alert(data.message || 'Gagal menghapus lampiran.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan: ' + err.message);
            });
    }

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAttachmentViewModal();
            closeAttachmentUploadModal();
        }
    });
</script>
@endpush
@endsection