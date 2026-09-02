@extends('layouts.app', [
'title' => 'Master Data - KMI Activity Plan',
'pageTitle' => 'MASTER DATA MANAGEMENT',
'pageSubtitle' => '<span>Departments</span> &bull; <span>Sub Departments</span> &bull; <span>Project Types</span> &bull; <span>Users</span>',
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
</script>
@endsection