@extends('layouts.app', [
'title' => 'Edit Project - ' . $project->txtProjectName,
'pageTitle' => 'EDIT PROJECT',
'pageSubtitle' => '<span>' . $project->txtProjectName . '</span>',
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight m-0">Edit Data Project</h2>
            <p class="text-xs text-gray-500 m-0">Perbarui informasi project, deliverable, skala grade, bobot, atau stage exposure.</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-2 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Detail</span>
        </a>
    </div>

    <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6" id="projectForm">
        @csrf
        @method('PUT')

        <!-- 1. Project Type & Structure Selector -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-sitemap"></i>
                <span>1. Jenis Struktur Project</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="p-4 rounded-2xl border-2 {{ ! $project->bitHasSubProject ? 'border-[#006838] bg-emerald-50/30' : 'border-gray-200' }} cursor-pointer transition flex items-start gap-3.5">
                    <input type="radio" name="bitHasSubProject" value="0" {{ ! $project->bitHasSubProject ? 'checked' : '' }} onchange="toggleProjectStructure(false)" class="mt-1 text-[#006838] focus:ring-[#006838]">
                    <div>
                        <div class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-cube text-blue-600"></i>
                            <span>Single Project</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 m-0">Project mandiri dengan stage exposure langsung di project utama.</p>
                    </div>
                </label>

                <label class="p-4 rounded-2xl border-2 {{ $project->bitHasSubProject ? 'border-[#006838] bg-emerald-50/30' : 'border-gray-200' }} cursor-pointer transition flex items-start gap-3.5">
                    <input type="radio" name="bitHasSubProject" value="1" {{ $project->bitHasSubProject ? 'checked' : '' }} onchange="toggleProjectStructure(true)" class="mt-1 text-[#006838] focus:ring-[#006838]">
                    <div>
                        <div class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-purple-600"></i>
                            <span>Project dengan Sub Projects</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 m-0">Project utama memiliki beberapa sub-project.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. Informasi Pokok Project -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i>
                <span>2. Informasi Utama Project</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Project <span class="text-red-500">*</span></label>
                    <input type="text" name="txtProjectName" value="{{ old('txtProjectName', $project->txtProjectName) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kode Project</label>
                    <input type="text" name="txtProjectCode" value="{{ old('txtProjectCode', $project->txtProjectCode) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tipe Project <span class="text-red-500">*</span></label>
                    <select name="intProjectType_ID" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                        @foreach ($projectTypes as $pt)
                        <option value="{{ $pt->intProjectType_ID }}" {{ old('intProjectType_ID', $project->intProjectType_ID) == $pt->intProjectType_ID ? 'selected' : '' }}>
                            {{ $pt->txtProjectTypeCode }} - {{ $pt->txtProjectTypeName }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sub Department <span class="text-red-500">*</span></label>
                    <select name="intSubDepartment_ID" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                        @foreach ($subDepartments as $sd)
                        <option value="{{ $sd->intSubDepartment_ID }}" {{ old('intSubDepartment_ID', $project->intSubDepartment_ID) == $sd->intSubDepartment_ID ? 'selected' : '' }}>
                            {{ $sd->txtSubDepartmentCode }} - {{ $sd->txtSubDepartmentName }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="intUser_ID" id="mainIntUserId" value="{{ old('intUser_ID', $project->intUser_ID) }}">

                <div class="md:col-span-2 {{ $project->bitHasSubProject ? 'hidden' : '' }}" id="singleAssignmentContainerWrapper">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Assignment Karyawan (Pelaksana Project) <span class="text-red-500">*</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mb-1.5">Project ini merupakan milik dari karyawan-karyawan yang dipilih di bawah (multiple assignment live search):</p>
                    <div id="singleProjectAssignmentContainer"></div>
                </div>

                <div class="md:col-span-2 {{ $project->bitHasSubProject ? '' : 'hidden' }}" id="multiAssignmentNotice">
                    <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-200 text-xs text-purple-900 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shrink-0 shadow-2xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <div class="font-bold">Penugasan Karyawan (Sub-Project Level)</div>
                            <p class="text-[11px] text-gray-600 m-0 mt-0.5">Untuk struktur <strong>Project dengan Sub Projects</strong>, input assignment karyawan ditentukan secara spesifik pada masing-masing sub-project di formulir sub-project di bawah.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">KPI Level <span class="text-red-500">*</span></label>
                    <select name="txtKpiLevel" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                        <option value="Individu" {{ old('txtKpiLevel', $project->txtKpiLevel) == 'Individu' ? 'selected' : '' }}>Individu</option>
                        <option value="Department" {{ old('txtKpiLevel', $project->txtKpiLevel) == 'Department' ? 'selected' : '' }}>Department</option>
                        <option value="MAR Bersama" {{ old('txtKpiLevel', $project->txtKpiLevel) == 'MAR Bersama' ? 'selected' : '' }}>MAR Bersama</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Objective Weight (Bobot KPI) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="floatWeight" value="{{ old('floatWeight', $project->floatWeight) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="dtmProjectStartDate" value="{{ old('dtmProjectStartDate', $project->dtmProjectStartDate?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="dtmProjectEndDate" value="{{ old('dtmProjectEndDate', $project->dtmProjectEndDate?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>
            </div>
        </div>

        <!-- 3. KPI Deliverable & Scoring Scale Matrix -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
            <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                <i class="fa-solid fa-list-check"></i>
                <span>3. Deliverable & Target Skala Grade KPI</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Deliverable</label>
                    <input type="text" name="txtDeliverable" value="{{ old('txtDeliverable', $project->txtDeliverable) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Achievement (Realisasi)</label>
                    <input type="text" name="txtAchievement" value="{{ old('txtAchievement', $project->txtAchievement) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Target Skala Grade (1 s/d 5)</label>
                    <textarea name="txtTargetSkalaGrade" rows="4"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none font-mono">{{ old('txtTargetSkalaGrade', $project->txtTargetSkalaGrade) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Score (1 s/d 5)</label>
                    <select name="intScore" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                        <option value="">Belum dinilai</option>
                        @for ($s = 1; $s <= 5; $s++)
                            <option value="{{ $s }}" {{ old('intScore', $project->intScore) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endfor
                    </select>
                </div>
            </div>
        </div>

        <!-- 4A. Single Project Stages -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 {{ $project->bitHasSubProject ? 'hidden' : '' }}" id="sectionSingleStages">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                    <i class="fa-solid fa-chart-gantt"></i>
                    <span>4. Tahapan Exposure S-Curve (Direct Stages)</span>
                </h3>
                <button type="button" onclick="addSingleStageRow()" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-[#006838] hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Stage</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left" id="singleStagesTable">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold">
                        <tr>
                            <th class="py-2.5 px-3">Nama Tahapan (Step)</th>
                            <th class="py-2.5 px-3">Start Date</th>
                            <th class="py-2.5 px-3">End Date</th>
                            <th class="py-2.5 px-3 text-center w-24">Plan (%)</th>
                            <th class="py-2.5 px-3 text-center w-24">Actual (%)</th>
                            <th class="py-2.5 px-3 text-center w-12">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="singleStagesBody">
                        @forelse ($project->directStages as $idx => $st)
                        <tr class="stage-row">
                            <td class="p-2"><input type="text" name="stages[{{ $idx }}][step]" value="{{ $st->txtProjectStageStep }}" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[{{ $idx }}][start]" value="{{ $st->dtmProjectStageStartDate?->format('Y-m-d') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[{{ $idx }}][end]" value="{{ $st->dtmProjectStageEndDate?->format('Y-m-d') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[{{ $idx }}][plan]" value="{{ $st->floatProjectStagePlan }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[{{ $idx }}][actual]" value="{{ $st->floatProjectStageActual }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                        @empty
                        <tr class="stage-row">
                            <td class="p-2"><input type="text" name="stages[0][step]" value="Initiation" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[0][start]" value="{{ date('Y-01-05') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="date" name="stages[0][end]" value="{{ date('Y-03-30') }}" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[0][plan]" value="50" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2"><input type="number" step="1" name="stages[0][actual]" value="50" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
                            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4B. Multi Sub-Projects Section -->
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 {{ ! $project->bitHasSubProject ? 'hidden' : '' }}" id="sectionMultiSubProjects">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-purple-600"></i>
                        <span>4. Daftar Sub Projects & Tahapan Exposure S-Curve</span>
                    </h3>
                    <p class="text-xs text-gray-500 m-0 mt-0.5">Setiap sub-project memiliki bobot, score, deliverable, periode, dan tahapan S-Curve sendiri.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div id="subProjectTotalWeightBadge" class="px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-xs font-bold text-purple-900 flex items-center gap-1.5">
                        <span>Total Bobot:</span>
                        <span id="subProjectTotalWeightVal">0%</span>
                    </div>
                    <button type="button" onclick="openAddSubProjectModal()" class="px-3.5 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Sub Project</span>
                    </button>
                </div>
            </div>

            <!-- Empty State Container -->
            <div id="subProjectsEmptyState" class="hidden py-10 text-center border-2 border-dashed border-purple-200 rounded-2xl bg-purple-50/20">
                <i class="fa-solid fa-layer-group text-3xl text-purple-300 mb-2"></i>
                <p class="text-xs font-bold text-gray-700 m-0">Belum ada Sub Project yang ditambahkan.</p>
                <p class="text-[11px] text-gray-500 m-0 mt-1">Klik tombol <strong>"+ Tambah Sub Project"</strong> untuk mengisi detail sub project beserta tahapan exposure S-Curve.</p>
            </div>

            <!-- Sub Projects Cards Container -->
            <div class="space-y-3" id="subProjectsContainer"></div>

            <!-- Hidden Inputs Container for form submission -->
            <div id="subProjectsHiddenInputs"></div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('projects.show', $project) }}" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm transition no-underline">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<!-- Modal Tambah / Edit Sub Project -->
@push('modals')
<div class="modal-overlay" id="subProjectModal" data-modal-overlay style="z-index: 2500;">
    <div class="modal-container modal-container-2xl !max-w-5xl w-full max-h-[92vh] flex flex-col p-0 overflow-hidden rounded-3xl" role="dialog" aria-modal="true" aria-labelledby="subModalTitle">
        <!-- Modal Header -->
        <div class="modal-header px-6 py-4 bg-purple-50/80 border-b border-purple-100 flex items-center justify-between">
            <div class="modal-title-copy flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-xs">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <h3 id="subModalTitle" class="text-base font-extrabold text-gray-900 m-0">Tambah Sub Project</h3>
                    <p class="text-xs text-gray-500 m-0 mt-0.5">Lengkapi data sub project dan tahapan exposure S-Curve (Direct Stages).</p>
                </div>
            </div>
            <button class="btn-close text-gray-400 hover:text-gray-600 p-2 cursor-pointer" type="button" onclick="closeModal('subProjectModal')" aria-label="Close modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="modal-body px-6 py-5 overflow-y-auto space-y-6 flex-1">
            <!-- 1. Detail Utama Sub Project -->
            <div class="space-y-3">
                <h4 class="text-xs font-black text-purple-950 uppercase tracking-wider m-0 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-purple-600"></i>
                    <span>A. Informasi Utama Sub Project</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 bg-gray-50/70 p-4 rounded-2xl border border-gray-200">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Sub Project <span class="text-red-500">*</span></label>
                        <input type="text" id="subModalName" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none bg-white" placeholder="Contoh: Vibe Coding / KIMI Agent / RPA Automation">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Bobot Sub (%) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" min="0" max="100" id="subModalWeight" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 outline-none bg-white" placeholder="Contoh: 35">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Mulai (Start)</label>
                        <input type="date" id="subModalStartDate" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-sm focus:border-purple-600 outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tanggal Selesai (Target)</label>
                        <input type="date" id="subModalEndDate" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-sm focus:border-purple-600 outline-none bg-white">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Deliverable / Output</label>
                        <input type="text" id="subModalDeliverable" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-sm focus:border-purple-600 outline-none bg-white" placeholder="Contoh: Modul otomatisasi & dokumentasi API">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Assignment Karyawan Sub Project <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-500 mb-1.5">Sub project ini milik dari karyawan-karyawan yang dipilih di bawah (multiple assignment live search):</p>
                        <div id="subModalAssignmentContainer"></div>
                    </div>
                </div>
            </div>

            <!-- 2. Tahapan Exposure S-Curve (Direct Stages) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-purple-950 uppercase tracking-wider m-0 flex items-center gap-2">
                            <i class="fa-solid fa-chart-gantt text-emerald-600"></i>
                            <span>B. Tahapan Exposure S-Curve (Direct Stages)</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 m-0 mt-0.5">Tentukan tahapan kerja (stages) untuk sub project ini. Akumulasi Plan disarankan 100%.</p>
                    </div>
                    <button type="button" onclick="addSubStageRow()" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-[#006838] hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer border border-emerald-200">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Tahapan</span>
                    </button>
                </div>

                <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3">Nama Tahapan (Step)</th>
                                    <th class="py-2.5 px-3">Start Date</th>
                                    <th class="py-2.5 px-3">End Date</th>
                                    <th class="py-2.5 px-3 text-center w-20">Plan (%)</th>
                                    <th class="py-2.5 px-3 text-center w-20">Actual (%)</th>
                                    <th class="py-2.5 px-3 text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="subModalStagesBody">
                                <!-- Populated dynamically -->
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold text-gray-700 border-t border-gray-200">
                                <tr>
                                    <td colspan="3" class="py-2 px-3 text-right text-[11px]">Total Alokasi Tahapan:</td>
                                    <td class="py-2 px-3 text-center" id="subModalTotalPlan">0%</td>
                                    <td class="py-2 px-3 text-center" id="subModalTotalActual">0%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <button type="button" onclick="closeModal('subProjectModal')" class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs transition cursor-pointer">
                Batal
            </button>
            <button type="button" onclick="saveSubProjectFromModal()" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-check"></i>
                <span id="subModalSubmitText">Simpan Sub Project</span>
            </button>
        </div>
    </div>
</div>
@endpush

@php
$employeesJson = $employees->map(function ($emp) {
return [
'id' => (int) $emp->intUser_ID,
'name' => $emp->txtEmployeeName,
'code' => $emp->txtEmployeeCode ?: '-',
'subdept' => $emp->subDepartment?->txtSubDepartmentCode ?: '-',
];
})->values()->all();

$initialSubProjects = $project->subProjects->map(function ($sp) {
return [
'id' => $sp->intSubProject_ID,
'name' => $sp->txtSubProjectName,
'weight' => (float) $sp->floatWeight,
'start_date' => $sp->dtmStartDate?->format('Y-m-d') ?: '',
'end_date' => $sp->dtmEndDate?->format('Y-m-d') ?: '',
'deliverable' => $sp->txtDeliverable ?: '',
'assignments' => $sp->assignments->pluck('intUser_ID')->values()->all(),
'stages' => $sp->stages->map(function ($st) {
return [
'id' => $st->intProjectStage_ID,
'step' => $st->txtProjectStageStep,
'start' => $st->dtmProjectStageStartDate?->format('Y-m-d') ?: '',
'end' => $st->dtmProjectStageEndDate?->format('Y-m-d') ?: '',
'plan' => (float) $st->floatProjectStagePlan,
'actual' => (float) $st->floatProjectStageActual,
];
})->values()->all(),
];
})->values()->all();

$authUserId = (int) ($authUser?->intUser_ID ?: 1);
$initialSingleAssignments = $project->directAssignments->pluck('intUser_ID')->values()->all();
if (empty($initialSingleAssignments) && $project->intUser_ID) {
$initialSingleAssignments = [$project->intUser_ID];
}
$nextSingleStageIndex = $project->directStages->count() + 1;
@endphp

@push('scripts')
<script>
    const allEmployees = @json($employeesJson);
    const initialSingleStageIndex = Number(@json($nextSingleStageIndex));
    const currentAuthUserId = Number(@json($authUserId));
    let singleStageIndex = initialSingleStageIndex;
    let currentEditingSubIndex = -1;

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
                <div class="multiselect-input-box min-h-[46px] p-2 rounded-xl border-2 border-emerald-600 bg-white flex flex-wrap items-center gap-1.5 cursor-text transition shadow-2xs focus-within:ring-2 focus-within:ring-emerald-500/20">
                    <div class="selected-chips-container flex flex-wrap items-center gap-1.5"></div>
                    <input type="text" class="search-input flex-1 min-w-[120px] px-2 py-1 text-xs sm:text-sm text-gray-800 placeholder-gray-400 bg-transparent outline-none border-none" placeholder="${config.placeholder || 'Search'}">
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
                chip.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#009ca6] hover:bg-[#008a90] text-white text-xs font-bold shadow-2xs transition select-none';
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
                    item.className = 'px-3.5 py-2.5 text-xs sm:text-sm font-bold bg-[#006838] text-white flex items-center justify-between cursor-pointer transition';
                    item.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>${escapeHtml(emp.name)}</span>
                            <span class="text-[11px] opacity-80 font-normal">(${escapeHtml(emp.code || '-')} - ${escapeHtml(emp.subdept || '-')})</span>
                        </div>
                        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-bold">Terpilih</span>
                    `;
                } else {
                    item.className = 'px-3.5 py-2.5 text-xs sm:text-sm text-gray-800 hover:bg-emerald-50 hover:text-[#006838] flex items-center justify-between cursor-pointer transition';
                    item.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span>${escapeHtml(emp.name)}</span>
                            <span class="text-[11px] text-gray-400 font-normal">(${escapeHtml(emp.code || '-')} - ${escapeHtml(emp.subdept || '-')})</span>
                        </div>
                    `;
                }

                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleId(Number(emp.id));
                    searchQuery = '';
                    searchInput.value = '';
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
            onChange(selectedIds.slice());
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

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && searchInput.value === '' && selectedIds.length > 0) {
                selectedIds.pop();
                renderChips();
                renderDropdown();
                onChange(selectedIds.slice());
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) {
                closeDropdown();
            }
        });

        renderChips();

        return {
            getSelected: () => selectedIds.slice(),
            setSelected: (ids) => {
                selectedIds = (ids || []).map(id => Number(id));
                renderChips();
                if (isOpen) renderDropdown();
                onChange(selectedIds.slice());
            },
            clear: () => {
                selectedIds = [];
                renderChips();
                if (isOpen) renderDropdown();
                onChange([]);
            }
        };
    }

    // Sub projects in-memory state loaded from DB
    let subProjectsList = @json($initialSubProjects);
    let singleAssignmentSelect = null;
    let subAssignmentSelect = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Single Project Multi-Select
        const initialSingleAssignments = @json($initialSingleAssignments);
        singleAssignmentSelect = createEmployeeMultiSelect({
            container: 'singleProjectAssignmentContainer',
            hiddenInputName: 'assignments[]',
            employees: allEmployees,
            selectedIds: initialSingleAssignments,
            placeholder: 'Ketik nama / kode karyawan untuk live search...',
            onChange: function(ids) {
                const hiddenUserId = document.getElementById('mainIntUserId');
                if (hiddenUserId && ids.length > 0) {
                    hiddenUserId.value = ids[0];
                }
            }
        });

        // Initialize Sub Project Modal Multi-Select
        subAssignmentSelect = createEmployeeMultiSelect({
            container: 'subModalAssignmentContainer',
            hiddenInputName: '',
            employees: allEmployees,
            selectedIds: [],
            placeholder: 'Ketik nama / kode karyawan untuk live search...'
        });

        renderSubProjects();
    });

    function toggleProjectStructure(hasSub) {
        const singleSec = document.getElementById('sectionSingleStages');
        const multiSec = document.getElementById('sectionMultiSubProjects');
        const singleAssignment = document.getElementById('singleAssignmentContainerWrapper');
        const multiNotice = document.getElementById('multiAssignmentNotice');

        if (hasSub) {
            singleSec.classList.add('hidden');
            multiSec.classList.remove('hidden');
            if (singleAssignment) singleAssignment.classList.add('hidden');
            if (multiNotice) multiNotice.classList.remove('hidden');
        } else {
            singleSec.classList.remove('hidden');
            multiSec.classList.add('hidden');
            if (singleAssignment) singleAssignment.classList.remove('hidden');
            if (multiNotice) multiNotice.classList.add('hidden');
        }
    }

    function addSingleStageRow() {
        const tbody = document.getElementById('singleStagesBody');
        const tr = document.createElement('tr');
        tr.className = 'stage-row';
        tr.innerHTML = `
            <td class="p-2"><input type="text" name="stages[${singleStageIndex}][step]" placeholder="Nama Tahapan" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="date" name="stages[${singleStageIndex}][start]" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="date" name="stages[${singleStageIndex}][end]" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
            <td class="p-2"><input type="number" step="1" name="stages[${singleStageIndex}][plan]" value="25" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
            <td class="p-2"><input type="number" step="1" name="stages[${singleStageIndex}][actual]" value="0" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
            <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1 cursor-pointer"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        singleStageIndex++;
    }

    // Modal Operations
    function openAddSubProjectModal() {
        currentEditingSubIndex = -1;
        document.getElementById('subModalTitle').textContent = 'Tambah Sub Project Baru';
        document.getElementById('subModalSubmitText').textContent = 'Tambahkan ke Daftar Sub Project';

        // Clear fields with defaults
        document.getElementById('subModalName').value = '';

        // Suggest remaining weight
        const curTotalWeight = subProjectsList.reduce((acc, curr) => acc + (parseFloat(curr.weight) || 0), 0);
        const remWeight = Math.max(0, 100 - curTotalWeight);
        document.getElementById('subModalWeight').value = remWeight > 0 ? remWeight : 25;

        // Defaults from main project
        const mainStartDate = document.querySelector('input[name="dtmProjectStartDate"]')?.value || '{{ date("Y-01-01") }}';
        const mainEndDate = document.querySelector('input[name="dtmProjectEndDate"]')?.value || '{{ date("Y-12-31") }}';
        document.getElementById('subModalStartDate').value = mainStartDate;
        document.getElementById('subModalEndDate').value = mainEndDate;
        document.getElementById('subModalDeliverable').value = '';

        if (subAssignmentSelect) {
            subAssignmentSelect.setSelected([currentAuthUserId]);
        }

        // Clear & populate 4 default stages
        const stagesBody = document.getElementById('subModalStagesBody');
        stagesBody.innerHTML = '';
        const defaultStages = [{
                step: 'Initiation & Requirement Mapping',
                start: mainStartDate,
                end: mainEndDate,
                plan: 25,
                actual: 0
            },
            {
                step: 'Development / Execution',
                start: mainStartDate,
                end: mainEndDate,
                plan: 35,
                actual: 0
            },
            {
                step: 'Trial & Validation',
                start: mainStartDate,
                end: mainEndDate,
                plan: 25,
                actual: 0
            },
            {
                step: 'Evaluation & Handover',
                start: mainStartDate,
                end: mainEndDate,
                plan: 15,
                actual: 0
            }
        ];
        defaultStages.forEach(st => addSubStageRow(st));
        calcSubModalStageTotals();

        openModal('subProjectModal');
    }

    function openEditSubProjectModal(index) {
        if (!subProjectsList[index]) return;
        currentEditingSubIndex = index;
        const sub = subProjectsList[index];

        document.getElementById('subModalTitle').textContent = `Edit Sub Project: ${sub.name}`;
        document.getElementById('subModalSubmitText').textContent = 'Simpan Perubahan';

        document.getElementById('subModalName').value = sub.name || '';
        document.getElementById('subModalWeight').value = sub.weight || 0;
        document.getElementById('subModalStartDate').value = sub.start_date || '';
        document.getElementById('subModalEndDate').value = sub.end_date || '';
        document.getElementById('subModalDeliverable').value = sub.deliverable || '';

        if (subAssignmentSelect) {
            subAssignmentSelect.setSelected(sub.assignments || []);
        }

        const stagesBody = document.getElementById('subModalStagesBody');
        stagesBody.innerHTML = '';
        if (Array.isArray(sub.stages) && sub.stages.length > 0) {
            sub.stages.forEach(st => addSubStageRow(st));
        } else {
            addSubStageRow({
                step: 'Initiation',
                start: sub.start_date,
                end: sub.end_date,
                plan: 50,
                actual: 0
            });
            addSubStageRow({
                step: 'Execution',
                start: sub.start_date,
                end: sub.end_date,
                plan: 50,
                actual: 0
            });
        }
        calcSubModalStageTotals();

        openModal('subProjectModal');
    }

    function addSubStageRow(data = {}) {
        const tbody = document.getElementById('subModalStagesBody');
        const tr = document.createElement('tr');
        tr.className = 'sub-modal-stage-row hover:bg-gray-50/70 transition';
        tr.innerHTML = `
            <td class="p-2">
                <input type="text" class="sub-stage-step w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs focus:border-purple-600 outline-none" value="${escapeHtml(data.step || '')}" placeholder="Nama Tahapan">
            </td>
            <td class="p-2">
                <input type="date" class="sub-stage-start w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs focus:border-purple-600 outline-none" value="${data.start || ''}">
            </td>
            <td class="p-2">
                <input type="date" class="sub-stage-end w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs focus:border-purple-600 outline-none" value="${data.end || ''}">
            </td>
            <td class="p-2">
                <input type="number" step="1" min="0" class="sub-stage-plan w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center font-bold text-indigo-700 focus:border-purple-600 outline-none" value="${data.plan !== undefined ? data.plan : 25}" oninput="calcSubModalStageTotals()">
            </td>
            <td class="p-2">
                <input type="number" step="1" min="0" class="sub-stage-actual w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center font-bold text-[#006838] focus:border-purple-600 outline-none" value="${data.actual !== undefined ? data.actual : 0}" oninput="calcSubModalStageTotals()">
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="this.closest('tr').remove(); calcSubModalStageTotals();" class="text-red-400 hover:text-red-600 p-1 cursor-pointer" title="Hapus Tahapan">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        calcSubModalStageTotals();
    }

    function calcSubModalStageTotals() {
        const rows = document.querySelectorAll('#subModalStagesBody .sub-modal-stage-row');
        let totalPlan = 0;
        let totalActual = 0;

        rows.forEach(r => {
            const plan = parseFloat(r.querySelector('.sub-stage-plan')?.value) || 0;
            const actual = parseFloat(r.querySelector('.sub-stage-actual')?.value) || 0;
            totalPlan += plan;
            totalActual += actual;
        });

        const planEl = document.getElementById('subModalTotalPlan');
        const actEl = document.getElementById('subModalTotalActual');

        if (planEl) {
            planEl.textContent = `${totalPlan}%`;
            planEl.className = `py-2 px-3 text-center ${totalPlan === 100 ? 'text-emerald-700 bg-emerald-50 rounded-lg' : 'text-amber-700 bg-amber-50 rounded-lg'}`;
        }
        if (actEl) {
            actEl.textContent = `${totalActual}%`;
            actEl.className = 'py-2 px-3 text-center text-[#006838]';
        }
    }

    function saveSubProjectFromModal() {
        const name = document.getElementById('subModalName').value.trim();
        const weightVal = document.getElementById('subModalWeight').value;
        const weight = parseFloat(weightVal);

        if (!name) {
            alert('Nama sub project wajib diisi!');
            document.getElementById('subModalName').focus();
            return;
        }

        if (isNaN(weight) || weight <= 0) {
            alert('Bobot sub project wajib diisi angka lebih dari 0!');
            document.getElementById('subModalWeight').focus();
            return;
        }

        const assignments = subAssignmentSelect ? subAssignmentSelect.getSelected() : [];
        if (assignments.length === 0) {
            alert('Pilih minimal 1 karyawan pada Assignment Sub Project!');
            return;
        }

        // Collect stages
        const stageRows = document.querySelectorAll('#subModalStagesBody .sub-modal-stage-row');
        const stages = [];
        stageRows.forEach(r => {
            const step = r.querySelector('.sub-stage-step')?.value.trim();
            if (step) {
                stages.push({
                    step: step,
                    start: r.querySelector('.sub-stage-start')?.value || '',
                    end: r.querySelector('.sub-stage-end')?.value || '',
                    plan: parseFloat(r.querySelector('.sub-stage-plan')?.value) || 0,
                    actual: parseFloat(r.querySelector('.sub-stage-actual')?.value) || 0,
                });
            }
        });

        const subData = {
            id: currentEditingSubIndex >= 0 ? (subProjectsList[currentEditingSubIndex]?.id || null) : null,
            name: name,
            weight: weight,
            start_date: document.getElementById('subModalStartDate').value || '',
            end_date: document.getElementById('subModalEndDate').value || '',
            deliverable: document.getElementById('subModalDeliverable').value.trim(),
            assignments: assignments,
            stages: stages
        };

        if (currentEditingSubIndex >= 0 && currentEditingSubIndex < subProjectsList.length) {
            subProjectsList[currentEditingSubIndex] = subData;
        } else {
            subProjectsList.push(subData);
        }

        renderSubProjects();
        closeModal('subProjectModal');
    }

    function deleteSubProject(index) {
        if (confirm(`Yakin ingin menghapus sub project "${subProjectsList[index]?.name}"?`)) {
            subProjectsList.splice(index, 1);
            renderSubProjects();
        }
    }

    function renderSubProjects() {
        const container = document.getElementById('subProjectsContainer');
        const emptyState = document.getElementById('subProjectsEmptyState');
        const hiddenContainer = document.getElementById('subProjectsHiddenInputs');
        const totalWeightVal = document.getElementById('subProjectTotalWeightVal');
        const totalWeightBadge = document.getElementById('subProjectTotalWeightBadge');

        container.innerHTML = '';
        hiddenContainer.innerHTML = '';

        if (subProjectsList.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }

        let totalWeight = 0;

        subProjectsList.forEach((sub, i) => {
            totalWeight += (parseFloat(sub.weight) || 0);

            // Calculate progress preview from stages
            const stagePlanSum = (sub.stages || []).reduce((acc, st) => acc + (parseFloat(st.plan) || 0), 0);
            const stageActSum = (sub.stages || []).reduce((acc, st) => acc + (parseFloat(st.actual) || 0), 0);
            const estProgress = stagePlanSum > 0 ? Math.round((stageActSum / stagePlanSum) * 100) : 0;

            const assignedPills = (sub.assignments && sub.assignments.length > 0) ?
                sub.assignments.map(id => {
                    const emp = allEmployees.find(e => Number(e.id) === Number(id));
                    return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-[#009ca6] text-white text-[11px] font-bold shadow-2xs"><i class="fa-solid fa-user text-[9px]"></i> ${escapeHtml(emp ? emp.name : 'ID:' + id)}</span>`;
                }).join(' ') :
                `<span class="text-[11px] text-gray-400 italic">Belum ada karyawan di-assign</span>`;

            // Render Card
            const card = document.createElement('div');
            card.className = 'p-5 rounded-2xl bg-purple-50/40 border border-purple-200 shadow-2xs space-y-3.5 transition hover:shadow-xs';

            let stagesHtml = '';
            if (sub.stages && sub.stages.length > 0) {
                stagesHtml = `
                    <div class="mt-2.5 rounded-xl border border-purple-100/80 bg-white/90 overflow-hidden">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-purple-50/60 text-purple-900 font-bold border-b border-purple-100/60">
                                <tr>
                                    <th class="py-1.5 px-3">Tahapan (Step)</th>
                                    <th class="py-1.5 px-3">Periode</th>
                                    <th class="py-1.5 px-3 text-center w-16">Plan</th>
                                    <th class="py-1.5 px-3 text-center w-16">Actual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${sub.stages.map(st => `
                                    <tr>
                                        <td class="py-1.5 px-3 font-semibold text-gray-800">${escapeHtml(st.step)}</td>
                                        <td class="py-1.5 px-3 text-gray-500">${st.start || '-'} s/d ${st.end || '-'}</td>
                                        <td class="py-1.5 px-3 text-center font-bold text-indigo-700">${st.plan}%</td>
                                        <td class="py-1.5 px-3 text-center font-bold text-[#006838]">${st.actual}%</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                stagesHtml = `<div class="p-2.5 text-center text-gray-400 text-xs bg-white/80 rounded-xl border border-purple-100/50">Belum ada tahapan stage yang ditentukan.</div>`;
            }

            card.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-purple-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-600 text-white font-black text-xs">
                            <i class="fa-solid fa-cube text-[10px]"></i>
                            <span>#${i + 1}</span>
                        </span>
                        <h4 class="text-sm font-extrabold text-gray-900 m-0">${escapeHtml(sub.name)}</h4>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-800">
                            Bobot: ${sub.weight}%
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            ${(sub.stages || []).length} Tahapan (${estProgress}% Est. Progress)
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-auto">
                        <button type="button" onclick="openEditSubProjectModal(${i})" class="px-3 py-1.5 rounded-xl bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" onclick="deleteSubProject(${i})" class="px-2.5 py-1.5 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-700 font-bold text-xs transition flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Assignment badges row -->
                <div class="p-2.5 rounded-xl bg-white/80 border border-purple-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-gray-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-users text-[#009ca6]"></i>
                        <span>Pelaksana:</span>
                    </span>
                    <div class="flex items-center gap-1.5 flex-wrap">${assignedPills}</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar text-purple-500"></i>
                        <span>Periode: <strong>${sub.start_date || '-'}</strong> s/d <strong>${sub.end_date || '-'}</strong></span>
                    </div>
                    ${sub.deliverable ? `
                        <div class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-flag text-emerald-600"></i>
                            <span class="truncate">Output: <strong>${escapeHtml(sub.deliverable)}</strong></span>
                        </div>
                    ` : ''}
                </div>

                ${stagesHtml}
            `;

            container.appendChild(card);

            // Generate Hidden Inputs
            hiddenContainer.innerHTML += `
                <input type="hidden" name="sub_projects[${i}][id]" value="${escapeAttr(sub.id || '')}">
                <input type="hidden" name="sub_projects[${i}][name]" value="${escapeAttr(sub.name)}">
                <input type="hidden" name="sub_projects[${i}][weight]" value="${escapeAttr(sub.weight)}">
                <input type="hidden" name="sub_projects[${i}][start_date]" value="${escapeAttr(sub.start_date || '')}">
                <input type="hidden" name="sub_projects[${i}][end_date]" value="${escapeAttr(sub.end_date || '')}">
                <input type="hidden" name="sub_projects[${i}][deliverable]" value="${escapeAttr(sub.deliverable || '')}">
            `;

            if (sub.assignments && sub.assignments.length > 0) {
                sub.assignments.forEach(assigneeId => {
                    hiddenContainer.innerHTML += `<input type="hidden" name="sub_projects[${i}][assignments][]" value="${assigneeId}">`;
                });
            }

            if (sub.stages && sub.stages.length > 0) {
                sub.stages.forEach((st, j) => {
                    hiddenContainer.innerHTML += `
                        <input type="hidden" name="sub_projects[${i}][stages][${j}][step]" value="${escapeAttr(st.step)}">
                        <input type="hidden" name="sub_projects[${i}][stages][${j}][start]" value="${escapeAttr(st.start || '')}">
                        <input type="hidden" name="sub_projects[${i}][stages][${j}][end]" value="${escapeAttr(st.end || '')}">
                        <input type="hidden" name="sub_projects[${i}][stages][${j}][plan]" value="${escapeAttr(st.plan)}">
                        <input type="hidden" name="sub_projects[${i}][stages][${j}][actual]" value="${escapeAttr(st.actual)}">
                    `;
                });
            }
        });

        // Update Total Weight Badge
        totalWeightVal.textContent = `${totalWeight}%`;
        if (totalWeight === 100) {
            totalWeightBadge.className = 'px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-300 text-xs font-bold text-emerald-800 flex items-center gap-1.5';
        } else {
            totalWeightBadge.className = 'px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-300 text-xs font-bold text-amber-800 flex items-center gap-1.5';
        }
    }

    // Form submit validation
    document.getElementById('projectForm')?.addEventListener('submit', function(e) {
        const isMulti = document.querySelector('input[name="bitHasSubProject"]:checked')?.value === '1';
        if (!isMulti) {
            const assigned = singleAssignmentSelect ? singleAssignmentSelect.getSelected() : [];
            if (assigned.length === 0) {
                e.preventDefault();
                alert('Harap pilih minimal 1 karyawan pelaksana pada Assignment Karyawan!');
                return false;
            }
            const hiddenUserId = document.getElementById('mainIntUserId');
            if (hiddenUserId) {
                hiddenUserId.value = assigned[0];
            }
        } else {
            if (subProjectsList.length === 0) {
                e.preventDefault();
                alert('Untuk tipe Project dengan Sub Projects, tambahkan minimal 1 Sub Project!');
                return false;
            }
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttr(str) {
        if (str === undefined || str === null) return '';
        return String(str).replace(/"/g, '&quot;');
    }
</script>
@endpush
@endsection