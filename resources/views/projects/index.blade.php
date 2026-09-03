@extends('layouts.app', [
'title' => 'Daftar Project - KMI Activity Plan',
'pageTitle' => 'PROJECT MANAGEMENT',
'pageSubtitle' => '<span>Single & Multi Sub-Projects</span> &bull; <span>KPI Objective Tracking</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight m-0">Katalog Project & KPI</h2>
            <p class="text-xs text-gray-500 m-0">Kelola rencana project single maupun multi sub-project dengan bobot KPI tahunan.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="px-4 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline shrink-0">
            <i class="fa-solid fa-plus"></i>
            <span>Buat Project Baru</span>
        </a>
    </div>

    <!-- Summary Metrics Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Projects</span>
            <div class="text-xl font-black text-[#006838] mt-0.5">{{ $summary['totalProjects'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Single Projects</span>
            <div class="text-xl font-black text-blue-600 mt-0.5">{{ $summary['singleProjects'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Multi Sub-Projects</span>
            <div class="text-xl font-black text-purple-600 mt-0.5">{{ $summary['multiProjects'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Avg Progress</span>
            <div class="text-xl font-black text-[#8CC63F] mt-0.5">{{ $summary['avgProgress'] }}%</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs">
        <form method="GET" action="{{ route('projects.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tipe Project</label>
                <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <option value="">Semua Tipe</option>
                    @foreach ($projectTypes as $pt)
                    <option value="{{ $pt->intProjectType_ID }}" {{ request('type') == $pt->intProjectType_ID ? 'selected' : '' }}>
                        {{ $pt->txtProjectTypeCode }} - {{ $pt->txtProjectTypeName }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sub Department</label>
                <select name="subdept" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <option value="">Semua Sub Dept</option>
                    @foreach ($subDepartments as $sd)
                    <option value="{{ $sd->intSubDepartment_ID }}" {{ request('subdept') == $sd->intSubDepartment_ID ? 'selected' : '' }}>
                        {{ $sd->txtSubDepartmentCode }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Employee PIC</label>
                <select name="employee" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <option value="">Semua Employee</option>
                    @foreach ($employees as $emp)
                    <option value="{{ $emp->intUser_ID }}" {{ request('employee') == $emp->intUser_ID ? 'selected' : '' }}>
                        {{ $emp->txtEmployeeName }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">KPI Level</label>
                <select name="kpi_level" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <option value="">Semua Level</option>
                    <option value="Department" {{ request('kpi_level') == 'Department' ? 'selected' : '' }}>Department</option>
                    <option value="Individu" {{ request('kpi_level') == 'Individu' ? 'selected' : '' }}>Individu</option>
                    <option value="MAR Bersama" {{ request('kpi_level') == 'MAR Bersama' ? 'selected' : '' }}>MAR Bersama</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cari Project</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama project..."
                        class="w-full pl-8 pr-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Projects Table -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5">Nama Project & Deliverable</th>
                        <th class="px-4 py-3.5">Tipe & Level</th>
                        <th class="px-4 py-3.5">PIC / Assignment</th>
                        <th class="px-4 py-3.5 text-center">Bobot</th>
                        <th class="px-4 py-3.5 text-center">Score</th>
                        <th class="px-4 py-3.5">Achievement</th>
                        <th class="px-4 py-3.5">Exposure</th>
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($projects as $idx => $project)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3.5 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <a href="{{ route('projects.show', $project) }}" class="font-extrabold text-[#006838] hover:underline block leading-tight text-sm no-underline">
                                {{ $project->txtProjectName }}
                            </a>
                            @if ($project->bitHasSubProject)
                            <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px] font-bold">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>{{ $project->subProjects->count() }} Sub Projects</span>
                            </div>
                            @else
                            <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">
                                <i class="fa-solid fa-cube"></i>
                                <span>Single Project</span>
                            </div>
                            @endif
                            <p class="text-[11px] text-gray-500 m-0 mt-1 truncate">{{ $project->txtDeliverable ?: 'Deliverable: -' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" style="background-color: {{ $project->projectType?->txtColor ?? '#006838' }}">
                                {{ $project->projectType?->txtProjectTypeCode ?? 'IPP' }}
                            </span>
                            <span class="block text-[11px] text-gray-500 font-medium mt-1">{{ $project->txtKpiLevel }}</span>
                        </td>
                        <td class="px-4 py-3.5 max-w-[200px]">
                            @php
                            $assigned = $project->allAssignedUsers();
                            @endphp
                            @if ($assigned->isNotEmpty())
                            <div class="flex flex-wrap gap-1 items-center">
                                @foreach ($assigned->take(2) as $assignee)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#009ca6] text-white text-[10px] font-bold shadow-2xs" title="{{ $assignee->txtEmployeeName }}">
                                    <i class="fa-solid fa-user text-[8px]"></i>
                                    <span class="truncate max-w-[110px]">{{ $assignee->txtEmployeeName }}</span>
                                </span>
                                @endforeach
                                @if ($assigned->count() > 2)
                                <span class="px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold" title="{{ $assigned->skip(2)->pluck('txtEmployeeName')->join(', ') }}">
                                    +{{ $assigned->count() - 2 }}
                                </span>
                                @endif
                            </div>
                            <span class="text-[10px] text-emerald-700 font-semibold block mt-1">{{ $project->subDepartment?->txtSubDepartmentCode ?? '-' }}</span>
                            @else
                            <span class="font-bold text-gray-800 block text-xs">{{ $project->user?->txtEmployeeName ?? 'Unassigned' }}</span>
                            <span class="text-[11px] text-emerald-700 font-semibold">{{ $project->subDepartment?->txtSubDepartmentCode ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center font-extrabold text-gray-900 text-sm">
                            {{ $project->floatWeight }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if ($project->intScore)
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-100 text-amber-900 font-black text-xs">
                                {{ $project->intScore }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-semibold text-gray-700">{{ $project->txtAchievement ?: '-' }}</span>
                        </td>
                        <td class="px-4 py-3.5 min-w-[120px]">
                            <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                <span class="text-gray-600">Actual</span>
                                <span class="text-[#006838]">{{ $project->floatActual }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full bg-[#8CC63F] rounded-full" style="width: {{ min(100, $project->floatActual) }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('projects.show', $project) }}" class="p-1.5 rounded-lg bg-gray-100 hover:bg-[#006838] hover:text-white text-gray-600 transition" title="Lihat Detail">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('projects.edit', $project) }}" class="p-1.5 rounded-lg bg-gray-100 hover:bg-amber-500 hover:text-white text-gray-600 transition" title="Edit">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </a>
                                <button type="button" onclick="confirmDelete('{{ route('projects.destroy', $project) }}', '{{ $project->txtProjectName }}')" class="p-1.5 rounded-lg bg-gray-100 hover:bg-red-500 hover:text-white text-gray-600 transition cursor-pointer" title="Hapus">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                            Tidak ada project yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function confirmDelete(actionUrl, itemName) {
        const form = document.getElementById('deleteConfirmForm');
        const title = document.getElementById('deleteConfirmTitle');
        const msg = document.getElementById('deleteConfirmMessage');
        const modal = document.getElementById('deleteConfirmModal');

        form.action = actionUrl;
        title.textContent = 'Hapus Project?';
        msg.textContent = `Apakah Anda yakin ingin menghapus project "${itemName}"?`;
        modal.classList.add('active');
    }
</script>
@endsection