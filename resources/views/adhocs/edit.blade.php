@extends('layouts.app', [
'title' => 'Edit Inisiatif Ad Hoc - ' . $adhoc->txtProjectName,
'pageTitle' => 'EDIT INISIATIF AD HOC',
'pageSubtitle' => '<span>' . ($adhoc->txtProjectCode ?: 'ADH') . '</span> &bull; <span>' . $adhoc->txtProjectName . '</span>',
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight m-0">Formulir Pembaruan Ad Hoc</h2>
            <p class="text-xs text-gray-500 m-0">Perbarui status, batas waktu sementara, tim gugus tugas, dan tahapan aksi.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('adhocs.show', $adhoc) }}" class="px-3.5 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-eye"></i>
            <a href="{{ route('adhocs.show', $adhoc) }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#006838] border border-emerald-300 font-semibold text-xs transition flex items-center gap-1.5 no-underline shadow-2xs" style="background-color: #ecfdf5; color: #006838; border-color: #a7f3d0;">
                <i class="fa-solid fa-eye text-[#006838]"></i>
                <span>Lihat Detail</span>
            </a>
            <a href="{{ route('adhocs.index') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
                <a href="{{ route('adhocs.show', $adhoc) }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#006838] border border-emerald-300 font-semibold text-xs transition flex items-center gap-1.5 no-underline shadow-2xs" style="background-color: #ecfdf5; color: #006838; border-color: #a7f3d0;">
                    <i class="fa-solid fa-eye text-[#006838]"></i>
                    <span>Lihat Detail</span>
                </a>
                <a href="{{ route('adhocs.index') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
        </div>
    </div>

    <form action="{{ route('adhocs.update', $adhoc) }}" method="POST" class="space-y-6" id="adHocEditForm">
        @csrf
        @method('PUT')

        <!-- 1. Identifikasi Situasi & Sasaran Khusus -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-bullseye"></i>
                <span>1. Identifikasi Situasi & Sasaran Khusus</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nama / Judul Kegiatan Ad Hoc <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="txtProjectName" value="{{ old('txtProjectName', $adhoc->txtProjectName) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 focus:ring-2 focus:ring-teal-700/20 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Kategori Ad Hoc <span class="text-red-500">*</span>
                    </label>
                    <select name="txtAdHocCategory" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                        <option value="">-- Pilih Kategori Situasi --</option>
                        @foreach (['Troubleshooting & Problem Solving', 'Special Request Management', 'Emergency Response', 'Audit & Compliance Finding', 'Process Improvement / Kaizen', 'Task Force Khusus'] as $cat)
                        <option value="{{ $cat }}" {{ old('txtAdHocCategory', $adhoc->txtAdHocCategory) == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Tingkat Urgensi / Prioritas <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @php
                            $currentPriority = old('txtPriority', $adhoc->txtPriority ?: 'Medium');
                        $currentPriority = old('txtPriority', $adhoc->txtPriority ?: 'Medium');
                        @endphp
                        @foreach (['Critical' => ['label' => 'Critical', 'icon' => 'fa-fire', 'active' => 'border-red-500 bg-red-50 text-red-700'], 'High' => ['label' => 'High', 'icon' => 'fa-triangle-exclamation', 'active' => 'border-orange-500 bg-orange-50 text-orange-700'], 'Medium' => ['label' => 'Medium', 'icon' => 'fa-circle-check', 'active' => 'border-blue-500 bg-blue-50 text-blue-700'], 'Low' => ['label' => 'Low', 'icon' => 'fa-circle-info', 'active' => 'border-gray-500 bg-gray-50 text-gray-700']] as $key => $u)
                        <label class="border-2 rounded-xl p-2 text-center cursor-pointer transition flex flex-col items-center justify-center gap-1 {{ $currentPriority == $key ? $u['active'] . ' font-black' : 'border-gray-200 text-gray-600 hover:border-teal-600' }}">
                            <input type="radio" name="txtPriority" value="{{ $key }}" {{ $currentPriority == $key ? 'checked' : '' }} class="hidden" onchange="updateUrgencyStyle(this)">
                            <i class="fa-solid {{ $u['icon'] }} text-xs"></i>
                            <span class="text-[11px] font-bold">{{ $u['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Sasaran / Tujuan Tunggal yang Ingin Dicapai <span class="text-red-500">*</span>
                    </label>
                    <textarea name="txtSpecialGoal" rows="2" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-teal-700 outline-none">{{ old('txtSpecialGoal', $adhoc->txtSpecialGoal) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Latar Belakang & Situasi Pemicu (Opsional)
                    </label>
                    <textarea name="txtDescription" rows="2"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-teal-700 outline-none">{{ old('txtDescription', $adhoc->txtDescription) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Sifat Sementara & Penugasan Tim -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>2. Sifat Sementara & Penugasan Tim Pelaksana</span>
                </h3>
                <span id="durationBadge" class="px-3 py-1 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 font-bold text-xs">
                    Durasi: - Hari
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="dtmProjectStartDate" id="startDateInput" value="{{ old('dtmProjectStartDate', $adhoc->dtmProjectStartDate?->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none" onchange="calculateDuration()">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Target Selesai (Batas Waktu Sementara) <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="dtmProjectEndDate" id="endDateInput" value="{{ old('dtmProjectEndDate', $adhoc->dtmProjectEndDate?->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none" onchange="calculateDuration()">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sub Department <span class="text-red-500">*</span></label>
                    <select name="intSubDepartment_ID" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                        @foreach ($subDepartments as $sd)
                        <option value="{{ $sd->intSubDepartment_ID }}" {{ old('intSubDepartment_ID', $adhoc->intSubDepartment_ID) == $sd->intSubDepartment_ID ? 'selected' : '' }}>
                            {{ $sd->txtSubDepartmentCode }} - {{ $sd->txtSubDepartmentName }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Skillset Terkait</label>
                    <select name="intSkillset_ID" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                        <option value="">-- Pilih Skillset Teknis --</option>
                        @foreach ($skillsets as $sk)
                        <option value="{{ $sk->intSkillset_ID }}" {{ old('intSkillset_ID', $adhoc->intSkillset_ID) == $sk->intSkillset_ID ? 'selected' : '' }}>
                            {{ $sk->txtSkillsetName }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="intUser_ID" id="mainIntUserId" value="{{ old('intUser_ID', $adhoc->intUser_ID) }}">
                <div class="md:col-span-2 p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#006838] text-white flex items-center justify-center text-sm shrink-0 shadow-2xs mt-0.5">
                            <i class="fa-brands fa-whatsapp text-white"></i>
                        </div>
                        <div class="flex-1 space-y-1">
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                Supervisor Penyetuju (ACC Ad Hoc)
                            </label>
                            <p class="text-[11px] text-gray-600 m-0 leading-relaxed">
                                Status Approval saat ini:
                                <strong class="font-bold {{ $adhoc->isPendingApproval() ? 'text-amber-700' : ($adhoc->isApproved() ? 'text-emerald-700' : 'text-rose-700') }}">
                                    {{ $adhoc->txtApprovalStatus ?: 'Approved' }}
                                </strong>
                                @if ($adhoc->txtApprovalNotes)
                                &bull; Catatan Review: <em>"{{ $adhoc->txtApprovalNotes }}"</em>
                                @endif
                            </p>
                            <div class="pt-2">
                                <select name="intSupervisor_ID" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none bg-white">
                                    <option value="">-- Pilih Supervisor Penyetuju --</option>
                                    @foreach ($supervisors as $spv)
                                    <option value="{{ $spv->intUser_ID }}" {{ old('intSupervisor_ID', $adhoc->intSupervisor_ID) == $spv->intUser_ID ? 'selected' : '' }}>
                                        {{ $spv->txtEmployeeName }} ({{ $spv->txtPosition ?? $spv->txtRole }}) {{ $spv->txtPhone ? '• WA: ' . $spv->txtPhone : '• WA: Belum terdaftar' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Tim Pelaksana Ad Hoc (Gugus Tugas) <span class="text-red-500">*</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mb-1.5">Pilih karyawan yang ditugaskan sebagai PIC utama dan anggota tim penanganan (live search multi-select):</p>
                    <div id="adhocAssignmentContainer"></div>
                </div>
            </div>
        </div>

        <!-- 3. Target Output & Kriteria Keberhasilan -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>3. Target Deliverable & Kriteria Keberhasilan</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Target Deliverable / Output Akhir</label>
                    <input type="text" name="txtDeliverable" value="{{ old('txtDeliverable', $adhoc->txtDeliverable) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Status Penanganan</label>
                    <select name="txtStatus" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                        @foreach (['In Progress', 'Under Review', 'Completed', 'Pending'] as $st)
                        <option value="{{ $st }}" {{ old('txtStatus', $adhoc->txtStatus) == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Kriteria Keberhasilan Penanganan (Definition of Done)
                    </label>
                    <textarea name="txtTargetSkalaGrade" rows="3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-teal-700 outline-none font-mono">{{ old('txtTargetSkalaGrade', $adhoc->txtTargetSkalaGrade) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Bobot Kontribusi Kinerja (%)</label>
                    <input type="number" step="0.5" min="0" max="100" name="floatWeight" value="{{ old('floatWeight', $adhoc->floatWeight) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-teal-700 outline-none">
                </div>
            </div>
        </div>

        <!-- 4. Tahapan Aksi Penanganan (Action Plan Stages) -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-teal-800 uppercase tracking-wider m-0 flex items-center gap-2">
                        <i class="fa-solid fa-list-ol"></i>
                        <span>4. Rencana Tahapan Aksi (Action Plan Steps)</span>
                    </h3>
                    <p class="text-xs text-gray-500 m-0 mt-0.5">Langkah-langkah terstruktur untuk menuntaskan sasaran ad hoc ini secara sistematis.</p>
                </div>
                <button type="button" onclick="addActionStepRow()" class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 hover:bg-teal-100 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                <button type="button" onclick="addActionStepRow()" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-[#006838] border border-emerald-300 hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer" style="background-color: #ecfdf5; color: #006838; border: 1px solid #6ee7b7;">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Langkah Aksi</span>
                </button>
                    <button type="button" onclick="addActionStepRow()" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-[#006838] border border-emerald-300 hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer" style="background-color: #ecfdf5; color: #006838; border: 1px solid #6ee7b7;">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Langkah Aksi</span>
                    </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left" id="actionStepsTable">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold">
                        <tr>
                            <th class="py-2.5 px-3">Langkah / Tindakan Penanganan</th>
                            <th class="py-2.5 px-3 w-40">Start Date</th>
                            <th class="py-2.5 px-3 w-40">End Date</th>
                            <th class="py-2.5 px-3 text-center w-24">Plan (%)</th>
                            <th class="py-2.5 px-3 text-center w-24">Actual (%)</th>
                            <th class="py-2.5 px-3 text-center w-12">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="actionStepsBody">
                        @forelse ($adhoc->directStages as $sIdx => $st)
                        <tr class="action-row">
                            <td class="p-2"><input type="text" name="stages[{{ $sIdx }}][step]" value="{{ $st->txtProjectStageStep }}" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[{{ $sIdx }}][start]" value="{{ $st->dtmProjectStageStartDate?->format('Y-m-d') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[{{ $sIdx }}][end]" value="{{ $st->dtmProjectStageEndDate?->format('Y-m-d') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[{{ $sIdx }}][plan]" value="{{ $st->floatProjectStagePlan }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[{{ $sIdx }}][actual]" value="{{ $st->floatProjectStageActual }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                        @empty
                        <tr class="action-row">
                            <td class="p-2"><input type="text" name="stages[0][step]" value="Identifikasi & Analisis Masalah" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[0][start]" value="{{ date('Y-m-d') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[0][end]" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[0][plan]" value="50" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[0][actual]" value="0" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('adhocs.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm transition no-underline">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer" style="background-color: #006838; color: #ffffff;">
                <i class="fa-solid fa-floppy-disk"></i>
                <i class="fa-solid fa-floppy-disk text-white"></i>
                <span>Perbarui Inisiatif Ad Hoc</span>
            </button>
        </div>
    </form>
</div>

@php
$assignedIds = $adhoc->assignments->pluck('intUser_ID')->unique()->values()->all();
if (empty($assignedIds) && $adhoc->intUser_ID) {
    $assignedIds = [$adhoc->intUser_ID];
$assignedIds = [$adhoc->intUser_ID];
}
$employeesJson = $employees->map(function ($e) {
    return [
        'id' => (int) $e->intUser_ID,
        'name' => $e->txtEmployeeName,
        'code' => $e->txtEmployeeCode ?? '',
        'role' => $e->txtRole,
        'subdept' => $e->subDepartment?->txtSubDepartmentCode ?? 'MDP',
    ];
return [
'id' => (int) $e->intUser_ID,
'name' => $e->txtEmployeeName,
'code' => $e->txtEmployeeCode ?? '',
'role' => $e->txtRole,
'subdept' => $e->subDepartment?->txtSubDepartmentCode ?? 'MDP',
];
})->values()->all();
@endphp

@push('scripts')
<script>
    const allEmployees = @json($employeesJson);
    const existingAssignedIds = @json($assignedIds);
    let stepIndex = {{ max(count($adhoc->directStages), 1) }};
    let stepIndex = {
        {
            max(count($adhoc - > directStages), 1)
        }
    };

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function calculateDuration() {
        const start = document.getElementById('startDateInput').value;
        const end = document.getElementById('endDateInput').value;
        const badge = document.getElementById('durationBadge');
        if (!start || !end) return;

        const d1 = new Date(start);
        const d2 = new Date(end);
        const diffTime = d2 - d1;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 0) {
            badge.textContent = `Durasi Penanganan: ${diffDays} Hari`;
            badge.className = 'px-3 py-1 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 font-bold text-xs';
        } else {
            badge.textContent = 'Tanggal Selesai harus >= Mulai';
            badge.className = 'px-3 py-1 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold text-xs';
        }
    }

    function updateUrgencyStyle(radio) {
        document.querySelectorAll('input[name="txtPriority"]').forEach(r => {
            const parent = r.closest('label');
            if (parent) {
                parent.className = 'border-2 rounded-xl p-2 text-center cursor-pointer transition flex flex-col items-center justify-center gap-1 border-gray-200 text-gray-600 hover:border-teal-600';
            }
        });
        const currentLabel = radio.closest('label');
        if (currentLabel) {
            const val = radio.value;
            let activeClass = 'border-blue-500 bg-blue-50 text-blue-700 font-black';
            if (val === 'Critical') activeClass = 'border-red-500 bg-red-50 text-red-700 font-black';
            if (val === 'High') activeClass = 'border-orange-500 bg-orange-50 text-orange-700 font-black';
            if (val === 'Low') activeClass = 'border-gray-500 bg-gray-50 text-gray-700 font-black';
            currentLabel.className = 'border-2 rounded-xl p-2 text-center cursor-pointer transition flex flex-col items-center justify-center gap-1 ' + activeClass;
        }
    }

    function addActionStepRow() {
        const tbody = document.getElementById('actionStepsBody');
        const tr = document.createElement('tr');
        tr.className = 'action-row';
        const startVal = document.getElementById('startDateInput').value || '{{ date('Y-m-d') }}';
        const endVal = document.getElementById('endDateInput').value || '{{ date('Y-m-d') }}';
        const startVal = document.getElementById('startDateInput').value || '{{ date('
        Y - m - d ') }}';
        const endVal = document.getElementById('endDateInput').value || '{{ date('
        Y - m - d ') }}';

        tr.innerHTML = `
            <td class="p-2"><input type="text" name="stages[${stepIndex}][step]" placeholder="Langkah tindakan..." class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="date" name="stages[${stepIndex}][start]" value="${startVal}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="date" name="stages[${stepIndex}][end]" value="${endVal}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="number" step="1" name="stages[${stepIndex}][plan]" value="25" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
            <td class="p-2"><input type="number" step="1" name="stages[${stepIndex}][actual]" value="0" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        stepIndex++;
    }

    // Component Factory for Live Search Multi-Select
    function createEmployeeMultiSelect(config) {
        const container = typeof config.container === 'string' ?
            document.getElementById(config.container) :
            config.container;
        if (!container) return null;

        const hiddenInputName = config.hiddenInputName || '';
        const employeesList = config.employees || [];
        let selectedIds = (config.selectedIds || []).map(id => Number(id));
        const onChange = config.onChange || function() {};
        let isOpen = false;
        let searchQuery = '';

        container.innerHTML = `
            <div class="employee-multiselect-root relative w-full font-sans select-none text-left">
                <div class="multiselect-input-box min-h-[46px] p-2 rounded-xl border-2 border-teal-700 bg-white flex flex-wrap items-center gap-1.5 cursor-text transition shadow-2xs focus-within:ring-2 focus-within:ring-teal-700/20">
                    <div class="selected-chips-container flex flex-wrap items-center gap-1.5"></div>
                    <input type="text" class="search-input flex-1 min-w-[120px] px-2 py-1 text-xs sm:text-sm text-gray-800 placeholder-gray-400 bg-transparent outline-none border-none" placeholder="${config.placeholder || 'Ketik nama karyawan...'}">
                </div>
                <div class="multiselect-dropdown hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl max-h-56 overflow-y-auto z-50 divide-y divide-gray-100"></div>
                <div class="hidden-inputs-container"></div>
            </div>
        `;

        const root = container.querySelector('.employee-multiselect-root');
        const inputBox = root.querySelector('.multiselect-input-box');
        const chipsContainer = root.querySelector('.selected-chips-container');
        const searchInput = root.querySelector('.search-input');
        const dropdown = root.querySelector('.multiselect-dropdown');
        const hiddenInputsContainer = root.querySelector('.hidden-inputs-container');

        function renderChips() {
            chipsContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';

            selectedIds.forEach(id => {
                const emp = employeesList.find(e => Number(e.id) === Number(id));
                const name = emp ? emp.name : 'User #' + id;

                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-2xs transition select-none';
                chip.innerHTML = `
                    <button type="button" class="chip-remove-btn w-4 h-4 rounded hover:bg-black/20 text-white font-extrabold flex items-center justify-center cursor-pointer text-xs leading-none" title="Hapus">&times;</button>
                    <span class="chip-label leading-tight">${escapeHtml(name)}</span>
                `;

                chip.querySelector('.chip-remove-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleId(id);
                    searchInput.focus();
                });

                chipsContainer.appendChild(chip);

                if (hiddenInputName) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = hiddenInputName;
                    hiddenInput.value = id;
                    hiddenInputsContainer.appendChild(hiddenInput);
                }
            });
        }

        function renderDropdown() {
            const query = searchQuery.trim().toLowerCase();
            const filtered = employeesList.filter(emp => {
                if (!query) return true;
                return (emp.name && emp.name.toLowerCase().includes(query)) ||
                    (emp.code && emp.code.toLowerCase().includes(query)) ||
                    (emp.subdept && emp.subdept.toLowerCase().includes(query));
            });

            dropdown.innerHTML = '';

            if (filtered.length === 0) {
                dropdown.innerHTML = '<div class="p-3 text-xs text-gray-400 text-center italic">Tidak ada karyawan yang cocok</div>';
                return;
            }

            filtered.forEach(emp => {
                const isSelected = selectedIds.includes(Number(emp.id));
                const item = document.createElement('div');

                if (isSelected) {
                    item.className = 'px-3.5 py-2.5 text-xs sm:text-sm font-bold bg-teal-700 text-white flex items-center justify-between cursor-pointer transition';
                    item.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>${escapeHtml(emp.name)}</span>
                        </div>
                        <span class="text-[10px] opacity-80 uppercase tracking-wider">${escapeHtml(emp.subdept || emp.role)}</span>
                    `;
                } else {
                    item.className = 'px-3.5 py-2.5 text-xs sm:text-sm text-gray-800 hover:bg-teal-50 flex items-center justify-between cursor-pointer transition';
                    item.innerHTML = `
                        <span>${escapeHtml(emp.name)}</span>
                        <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">${escapeHtml(emp.subdept || emp.role)}</span>
                    `;
                }

                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleId(emp.id);
                    searchInput.value = '';
                    searchQuery = '';
                    renderDropdown();
                    searchInput.focus();
                });

                dropdown.appendChild(item);
            });
        }

        function toggleId(id) {
            const numId = Number(id);
            const idx = selectedIds.indexOf(numId);
            if (idx > -1) {
                selectedIds.splice(idx, 1);
            } else {
                selectedIds.push(numId);
            }
            renderChips();
            renderDropdown();
            onChange([...selectedIds]);
        }

        function openDropdown() {
            isOpen = true;
            dropdown.classList.remove('hidden');
            renderDropdown();
        }

        function closeDropdown() {
            isOpen = false;
            dropdown.classList.add('hidden');
        }

        inputBox.addEventListener('click', () => {
            searchInput.focus();
            openDropdown();
        });

        searchInput.addEventListener('focus', () => {
            openDropdown();
        });

        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            openDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) {
                closeDropdown();
            }
        });

        renderChips();

        return {
            getSelectedIds: () => [...selectedIds],
            setSelectedIds: (ids) => {
                selectedIds = (ids || []).map(id => Number(id));
                renderChips();
                if (isOpen) renderDropdown();
                onChange([...selectedIds]);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        calculateDuration();

        createEmployeeMultiSelect({
            container: 'adhocAssignmentContainer',
            hiddenInputName: 'assignments[]',
            employees: allEmployees,
            selectedIds: existingAssignedIds,
            placeholder: 'Cari & tambahkan anggota tim...',
            onChange: function(ids) {
                if (ids.length > 0) {
                    document.getElementById('mainIntUserId').value = ids[0];
                }
            }
        });
    });
</script>
@endpush
@endsection

