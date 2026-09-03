@extends('layouts.app', [
'title' => 'Master Data - KMI Activity Plan',
'pageTitle' => 'MASTER DATA MANAGEMENT',
'pageSubtitle' => '<span>Departments</span> &bull; <span>Sub Departments</span> &bull; <span>Project Types</span> &bull; <span>Skillsets</span> &bull; <span>Users</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Master Data Sistem</h2>
            <p class="text-xs text-gray-500 m-0">Konfigurasi departemen, sub-departemen, tipe project KPI, dan data user employee.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white p-2 rounded-2xl border border-[#DDE5DD] shadow-2xs flex flex-wrap gap-2">
        <a href="{{ route('master.index', ['tab' => 'departments']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition no-underline flex items-center gap-2 {{ $tab === 'departments' ? 'bg-[#006838] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-building"></i>
            <span>Departments ({{ $departments->count() }})</span>
        </a>
        <a href="{{ route('master.index', ['tab' => 'subdepartments']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition no-underline flex items-center gap-2 {{ $tab === 'subdepartments' ? 'bg-[#006838] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-sitemap"></i>
            <span>Sub Departments ({{ $subDepartments->count() }})</span>
        </a>
        <a href="{{ route('master.index', ['tab' => 'project_types']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition no-underline flex items-center gap-2 {{ $tab === 'project_types' ? 'bg-[#006838] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-layer-group"></i>
            <span>Project Types ({{ $projectTypes->count() }})</span>
        </a>
        <a href="{{ route('master.index', ['tab' => 'skillsets']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition no-underline flex items-center gap-2 {{ $tab === 'skillsets' ? 'bg-[#006838] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-code"></i>
            <span>Skillsets ({{ $skillsets->count() }})</span>
        </a>
        <a href="{{ route('master.index', ['tab' => 'users']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition no-underline flex items-center gap-2 {{ $tab === 'users' ? 'bg-[#006838] text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-users"></i>
            <span>User & Employee ({{ $users->count() }})</span>
        </a>
    </div>

    <!-- Tab 1: Departments -->
    @if ($tab === 'departments')
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Daftar Department</h3>
            <button type="button" onclick="openModal('addDeptModal')" class="px-3.5 py-1.5 rounded-xl bg-[#006838] text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Department
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">ID</th>
                        <th class="p-3">Kode Dept</th>
                        <th class="p-3">Nama Department</th>
                        <th class="p-3">Keterangan</th>
                        <th class="p-3 text-center">Sub Depts</th>
                        <th class="p-3 text-center">Users</th>
                        <th class="p-3 text-center">Projects</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($departments as $dept)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $dept->intDepartment_ID }}</td>
                        <td class="p-3 font-black text-[#006838]">{{ $dept->txtDepartmentCode }}</td>
                        <td class="p-3 font-bold text-gray-800">{{ $dept->txtDepartmentName }}</td>
                        <td class="p-3 text-gray-500">{{ $dept->txtDescription ?: '-' }}</td>
                        <td class="p-3 text-center font-semibold">{{ $dept->sub_departments_count }}</td>
                        <td class="p-3 text-center font-semibold">{{ $dept->users_count }}</td>
                        <td class="p-3 text-center font-semibold">{{ $dept->projects_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addDeptModal">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Department</h3>
                <button type="button" onclick="closeModal('addDeptModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('master.departments.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Dept (e.g. ENG, QA, PROD)</label>
                    <input type="text" name="txtDepartmentCode" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Department</label>
                    <input type="text" name="txtDepartmentName" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan</label>
                    <textarea name="txtDescription" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addDeptModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] text-white text-xs font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tab 2: Sub-Departments -->
    @if ($tab === 'subdepartments')
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Daftar Sub Department</h3>
            <button type="button" onclick="openModal('addSubDeptModal')" class="px-3.5 py-1.5 rounded-xl bg-[#006838] text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Sub Department
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">ID</th>
                        <th class="p-3">Kode Sub Dept</th>
                        <th class="p-3">Nama Sub Department</th>
                        <th class="p-3">Department Induk</th>
                        <th class="p-3 text-center">Jumlah User</th>
                        <th class="p-3 text-center">Jumlah Project</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($subDepartments as $sd)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $sd->intSubDepartment_ID }}</td>
                        <td class="p-3 font-black text-[#006838]">{{ $sd->txtSubDepartmentCode }}</td>
                        <td class="p-3 font-bold text-gray-800">{{ $sd->txtSubDepartmentName }}</td>
                        <td class="p-3 text-emerald-700 font-semibold">{{ $sd->department?->txtDepartmentName ?? '-' }}</td>
                        <td class="p-3 text-center font-semibold">{{ $sd->users_count }}</td>
                        <td class="p-3 text-center font-semibold">{{ $sd->projects_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Sub Department Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addSubDeptModal">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Sub Department</h3>
                <button type="button" onclick="closeModal('addSubDeptModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('master.subdepartments.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Department</label>
                    <select name="intDepartment_ID" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                        @foreach ($departments as $dept)
                        <option value="{{ $dept->intDepartment_ID }}">{{ $dept->txtDepartmentCode }} - {{ $dept->txtDepartmentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Sub Dept (e.g. MD/IT, AM)</label>
                    <input type="text" name="txtSubDepartmentCode" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Sub Department</label>
                    <input type="text" name="txtSubDepartmentName" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan</label>
                    <textarea name="txtDescription" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addSubDeptModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] text-white text-xs font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tab 3: Project Types -->
    @if ($tab === 'project_types')
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Tipe Project KPI</h3>
            <button type="button" onclick="openModal('addProjectTypeModal')" class="px-3.5 py-1.5 rounded-xl bg-[#006838] text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Tipe
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">ID</th>
                        <th class="p-3">Kode Tipe</th>
                        <th class="p-3">Nama Tipe KPI</th>
                        <th class="p-3 text-center">Default Weight</th>
                        <th class="p-3">Badge & Warna</th>
                        <th class="p-3 text-center">Jumlah Project</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($projectTypes as $pt)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $pt->intProjectType_ID }}</td>
                        <td class="p-3 font-black text-gray-900">{{ $pt->txtProjectTypeCode }}</td>
                        <td class="p-3 font-bold text-gray-800">{{ $pt->txtProjectTypeName }}</td>
                        <td class="p-3 text-center font-black text-[#006838] text-sm">{{ $pt->floatDefaultWeight }}%</td>
                        <td class="p-3">
                            <span class="px-2.5 py-0.5 rounded-full text-white font-bold text-[10px]" style="background-color: {{ $pt->txtColor }}">
                                <i class="{{ $pt->txtIcon }} mr-1"></i> {{ $pt->txtProjectTypeCode }}
                            </span>
                        </td>
                        <td class="p-3 text-center font-semibold">{{ $pt->projects_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Project Type Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addProjectTypeModal">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Tipe Project</h3>
                <button type="button" onclick="closeModal('addProjectTypeModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('master.project-types.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Tipe (e.g. IPP, IDP, Routine)</label>
                    <input type="text" name="txtProjectTypeCode" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Tipe KPI</label>
                    <input type="text" name="txtProjectTypeName" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Default Weight (%)</label>
                        <input type="number" step="1" name="floatDefaultWeight" value="20" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Warna Badge</label>
                        <input type="color" name="txtColor" value="#006838" class="w-full h-9 rounded-xl border border-gray-300 cursor-pointer">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addProjectTypeModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] text-white text-xs font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tab: Skillsets -->
    @if ($tab === 'skillsets')
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0">Daftar Skillset / Keahlian Teknis</h3>
                <p class="text-[11px] text-gray-500 m-0 mt-0.5">Master bidang kompetensi atau keahlian untuk project utama.</p>
            </div>
            <button type="button" onclick="openModal('addSkillsetModal')" class="px-3.5 py-1.5 rounded-xl bg-[#006838] text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Skillset
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">ID</th>
                        <th class="p-3">Nama Skillset</th>
                        <th class="p-3">Deskripsi</th>
                        <th class="p-3">Badge & Icon</th>
                        <th class="p-3 text-center">Jumlah Project</th>
                        <th class="p-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($skillsets as $sk)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $sk->intSkillset_ID }}</td>
                        <td class="p-3 font-black text-gray-900">{{ $sk->txtSkillsetName }}</td>
                        <td class="p-3 text-gray-600 max-w-sm">{{ $sk->txtDescription ?: '-' }}</td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-white font-bold text-[11px] shadow-2xs" style="background-color: {{ $sk->txtBadgeColor ?: '#006838' }}">
                                <i class="{{ $sk->txtIcon ?: 'fa-solid fa-code' }}"></i>
                                <span>{{ $sk->txtSkillsetName }}</span>
                            </span>
                        </td>
                        <td class="p-3 text-center font-black text-[#006838]">{{ $sk->projects_count }}</td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="editSkillset({{ json_encode($sk) }})" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition" title="Edit Skillset">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('master.skillsets.destroy', $sk) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus/menonaktifkan skillset ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition" title="Hapus Skillset">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada data skillset.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
    $skillsetIcons = [
    ['icon' => 'fa-solid fa-users', 'label' => 'GROUP'],
    ['icon' => 'fa-solid fa-chalkboard-user', 'label' => 'WORKSHOP'],
    ['icon' => 'fa-solid fa-comments', 'label' => 'DISCUSSION'],
    ['icon' => 'fa-solid fa-book-open', 'label' => 'LEARNING'],
    ['icon' => 'fa-solid fa-lightbulb', 'label' => 'IDEA'],
    ['icon' => 'fa-solid fa-microphone', 'label' => 'TALK'],
    ['icon' => 'fa-solid fa-laptop-code', 'label' => 'TECH'],
    ['icon' => 'fa-solid fa-network-wired', 'label' => 'NETWORK'],
    ['icon' => 'fa-solid fa-robot', 'label' => 'AUTOMATION'],
    ['icon' => 'fa-solid fa-brain', 'label' => 'AI'],
    ['icon' => 'fa-solid fa-gears', 'label' => 'ENGINEERING'],
    ['icon' => 'fa-solid fa-handshake', 'label' => 'MENTORING'],
    ['icon' => 'fa-solid fa-globe', 'label' => 'WEB DEV'],
    ['icon' => 'fa-solid fa-microchip', 'label' => 'HARDWARE / IOT'],
    ['icon' => 'fa-solid fa-mobile-screen-button', 'label' => 'MOBILE'],
    ['icon' => 'fa-solid fa-cloud', 'label' => 'CLOUD'],
    ['icon' => 'fa-solid fa-database', 'label' => 'DATABASE'],
    ['icon' => 'fa-solid fa-chart-line', 'label' => 'ANALYTICS'],
    ['icon' => 'fa-solid fa-shield-halved', 'label' => 'SECURITY'],
    ['icon' => 'fa-solid fa-server', 'label' => 'SERVER'],
    ['icon' => 'fa-solid fa-terminal', 'label' => 'TERMINAL'],
    ['icon' => 'fa-solid fa-cube', 'label' => 'PRODUCT'],
    ['icon' => 'fa-solid fa-wrench', 'label' => 'MAINTENANCE'],
    ['icon' => 'fa-solid fa-bullseye', 'label' => 'TARGET'],
    ];
    @endphp

    <!-- Add Skillset Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addSkillsetModal">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Skillset Baru</h3>
                    <p class="text-xs text-gray-500 m-0">Buat kategori keahlian teknis untuk penugasan project.</p>
                </div>
                <button type="button" onclick="closeModal('addSkillsetModal')" class="text-gray-400 p-1 hover:text-gray-600 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="{{ route('master.skillsets.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Skillset <span class="text-red-500">*</span></label>
                    <input type="text" name="txtSkillsetName" required placeholder="Contoh: Web Development / AI & Computer Vision" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi</label>
                    <textarea name="txtDescription" rows="2" placeholder="Keterangan cakupan teknologi/keahlian..." class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none"></textarea>
                </div>

                <!-- Icon Selector Grid -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Icon Skillset <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-gray-600 font-bold flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-lg">
                            <span>Terpilih:</span>
                            <i id="addSkillsetIconPreview" class="fa-solid fa-robot text-[#006838] text-sm"></i>
                        </span>
                    </div>

                    <input type="hidden" name="txtIcon" id="addSkillsetIcon" value="fa-solid fa-robot">

                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 max-h-56 overflow-y-auto p-2 custom-scrollbar border border-gray-200 rounded-2xl bg-gray-50/50">
                        @foreach ($skillsetIcons as $item)
                        <button type="button"
                            onclick="selectSkillsetIcon('add', '{{ $item['icon'] }}', this)"
                            data-icon="{{ $item['icon'] }}"
                            class="skillset-icon-card-add flex flex-col items-center justify-center p-3 rounded-2xl border transition-all duration-150 cursor-pointer text-center group {{ $item['icon'] === 'fa-solid fa-robot' ? 'border-2 border-[#006838] bg-emerald-50/60 text-[#006838] font-black shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/80 text-gray-600' }}">
                            <i class="{{ $item['icon'] }} text-2xl mb-1.5 transition-transform group-hover:scale-110"></i>
                            <span class="text-[9px] uppercase font-bold tracking-wider leading-tight">{{ $item['label'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Warna Badge Kategori</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="txtBadgeColor" value="#006838" class="w-12 h-9 rounded-xl border border-gray-300 cursor-pointer p-0.5">
                        <span class="text-xs text-gray-500">Warna aksen latar badge untuk identifikasi di daftar project.</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeModal('addSkillsetModal')" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#00552e] text-white text-xs font-bold shadow-xs transition">Simpan Skillset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Skillset Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="editSkillsetModal">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Edit Skillset</h3>
                    <p class="text-xs text-gray-500 m-0">Perbarui nama, deskripsi, icon, atau warna skillset.</p>
                </div>
                <button type="button" onclick="closeModal('editSkillsetModal')" class="text-gray-400 p-1 hover:text-gray-600 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="editSkillsetForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Skillset <span class="text-red-500">*</span></label>
                    <input type="text" name="txtSkillsetName" id="editSkillsetName" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi</label>
                    <textarea name="txtDescription" id="editSkillsetDescription" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none"></textarea>
                </div>

                <!-- Icon Selector Grid for Edit -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Pilih Icon Skillset <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-gray-600 font-bold flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-lg">
                            <span>Terpilih:</span>
                            <i id="editSkillsetIconPreview" class="fa-solid fa-code text-[#006838] text-sm"></i>
                        </span>
                    </div>

                    <input type="hidden" name="txtIcon" id="editSkillsetIcon" value="fa-solid fa-code">

                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 max-h-56 overflow-y-auto p-2 custom-scrollbar border border-gray-200 rounded-2xl bg-gray-50/50">
                        @foreach ($skillsetIcons as $item)
                        <button type="button"
                            onclick="selectSkillsetIcon('edit', '{{ $item['icon'] }}', this)"
                            data-icon="{{ $item['icon'] }}"
                            class="skillset-icon-card-edit flex flex-col items-center justify-center p-3 rounded-2xl border transition-all duration-150 cursor-pointer text-center group border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/80 text-gray-600">
                            <i class="{{ $item['icon'] }} text-2xl mb-1.5 transition-transform group-hover:scale-110"></i>
                            <span class="text-[9px] uppercase font-bold tracking-wider leading-tight">{{ $item['label'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Warna Badge Kategori</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="txtBadgeColor" id="editSkillsetBadgeColor" class="w-12 h-9 rounded-xl border border-gray-300 cursor-pointer p-0.5">
                        <span class="text-xs text-gray-500">Warna aksen latar badge untuk identifikasi di daftar project.</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeModal('editSkillsetModal')" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#00552e] text-white text-xs font-bold shadow-xs transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tab 4: Users / Employees -->
    @if ($tab === 'users')
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-gray-900 m-0">Daftar Pengguna & Karyawan</h3>
            <button type="button" onclick="openModal('addUserModal')" class="px-3.5 py-1.5 rounded-xl bg-[#006838] text-white text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> Tambah User
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                    <tr>
                        <th class="p-3 w-12 text-center">ID</th>
                        <th class="p-3">Nama & NIK</th>
                        <th class="p-3">Email & WhatsApp</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Department & Sub Dept</th>
                        <th class="p-3">Supervisi Sub-Dept (SPV)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $u)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 text-center font-bold text-gray-400">{{ $u->intUser_ID }}</td>
                        <td class="p-3 font-bold text-gray-900">
                            {{ $u->txtEmployeeName }}
                            <span class="block text-[11px] text-gray-400 font-mono">{{ $u->txtEmployeeCode ?: '-' }}</span>
                        </td>
                        <td class="p-3 font-medium text-gray-700">
                            <div>{{ $u->txtEmail }}</div>
                            <div class="text-[11px] text-emerald-700 font-mono"><i class="fa-brands fa-whatsapp"></i> {{ $u->txtPhone ?: '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->txtRole === 'Head' ? 'bg-purple-100 text-purple-900' : ($u->txtRole === 'Supervisor' ? 'bg-blue-100 text-blue-900' : ($u->txtRole === 'Superadmin' ? 'bg-red-100 text-red-900' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $u->txtRole }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-gray-800">{{ $u->department?->txtDepartmentCode ?? 'MDP' }}</span>
                            <span class="block text-[11px] text-gray-500">{{ $u->subDepartment?->txtSubDepartmentCode ?? '-' }}</span>
                        </td>
                        <td class="p-3">
                            @if ($u->isSupervisor() && $u->supervisedSubDepartments->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($u->supervisedSubDepartments as $sup)
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-800 font-bold text-[9px] border border-blue-200">
                                    {{ $sup->txtSubDepartmentCode }}
                                </span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addUserModal">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah User Baru</h3>
                <button type="button" onclick="closeModal('addUserModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('master.users.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="txtEmployeeName" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">NIK / Kode</label>
                        <input type="text" name="txtEmployeeCode" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Kalbe</label>
                        <input type="email" name="txtEmail" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">WhatsApp Number</label>
                        <input type="text" name="txtPhone" required placeholder="628123456789" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Role</label>
                        <select name="txtRole" required id="modalRoleSelect" onchange="toggleSupervisedSubDepts(this.value)" class="w-full px-2.5 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                            <option value="Employee">Employee</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Head">Head</option>
                            <option value="Superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Department</label>
                        <select name="intDepartment_ID" class="w-full px-2.5 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                            @foreach ($departments as $dept)
                            <option value="{{ $dept->intDepartment_ID }}">{{ $dept->txtDepartmentCode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sub Dept</label>
                        <select name="intSubDepartment_ID" class="w-full px-2.5 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                            <option value="">-</option>
                            @foreach ($subDepartments as $sd)
                            <option value="{{ $sd->intSubDepartment_ID }}">{{ $sd->txtSubDepartmentCode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Multi-subdept selector for Supervisor -->
                <div id="spvMultiSubDeptSection" class="hidden p-3 rounded-2xl bg-blue-50/60 border border-blue-200 space-y-2">
                    <label class="block text-xs font-bold text-blue-900 uppercase">Supervisi Sub-Department (1 Supervisor bisa >1 Sub Dept)</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($subDepartments as $sd)
                        <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                            <input type="checkbox" name="supervised_subdepts[]" value="{{ $sd->intSubDepartment_ID }}" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>{{ $sd->txtSubDepartmentCode }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password</label>
                    <input type="password" name="txtPassword" value="123456" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] text-white text-xs font-bold">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function toggleSupervisedSubDepts(role) {
        const sec = document.getElementById('spvMultiSubDeptSection');
        if (role === 'Supervisor') {
            sec.classList.remove('hidden');
        } else {
            sec.classList.add('hidden');
        }
    }

    function selectSkillsetIcon(prefix, iconClass, el) {
        const input = document.getElementById(prefix + 'SkillsetIcon');
        const preview = document.getElementById(prefix + 'SkillsetIconPreview');
        if (input) input.value = iconClass;
        if (preview) preview.className = iconClass + ' text-[#006838] text-sm';

        const cards = document.querySelectorAll('.skillset-icon-card-' + prefix);
        cards.forEach(card => {
            card.className = 'skillset-icon-card-' + prefix + ' flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/80 text-gray-600 transition-all duration-150 cursor-pointer text-center group';
        });

        if (el) {
            el.className = 'skillset-icon-card-' + prefix + ' flex flex-col items-center justify-center p-3 rounded-2xl border-2 border-[#006838] bg-emerald-50/60 text-[#006838] font-black shadow-xs transition-all duration-150 cursor-pointer text-center group';
        }
    }

    function editSkillset(sk) {
        document.getElementById('editSkillsetName').value = sk.txtSkillsetName;
        document.getElementById('editSkillsetDescription').value = sk.txtDescription || '';
        document.getElementById('editSkillsetBadgeColor').value = sk.txtBadgeColor || '#006838';
        document.getElementById('editSkillsetForm').action = '/master-data/skillsets/' + sk.intSkillset_ID;

        const iconVal = sk.txtIcon || 'fa-solid fa-code';
        let matchedCard = null;
        const cards = document.querySelectorAll('.skillset-icon-card-edit');
        cards.forEach(card => {
            if (card.getAttribute('data-icon') === iconVal) {
                matchedCard = card;
            }
        });

        selectSkillsetIcon('edit', iconVal, matchedCard);
        openModal('editSkillsetModal');
    }
</script>
@endsection