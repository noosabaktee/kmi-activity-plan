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

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">PIC Employee <span class="text-red-500">*</span></label>
                    <select name="intUser_ID" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] outline-none">
                        @foreach ($employees as $emp)
                        <option value="{{ $emp->intUser_ID }}" {{ old('intUser_ID', $project->intUser_ID) == $emp->intUser_ID ? 'selected' : '' }}>
                            {{ $emp->txtEmployeeName }} ({{ $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                        </option>
                        @endforeach
                    </select>
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
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-[#006838] uppercase tracking-wider m-0 flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-purple-600"></i>
                        <span>4. Daftar Sub Projects & Bobot</span>
                    </h3>
                </div>
                <button type="button" onclick="addSubProjectCard()" class="px-3.5 py-2 rounded-xl bg-purple-100 text-purple-800 hover:bg-purple-200 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Sub Project</span>
                </button>
            </div>

            <div class="space-y-4" id="subProjectsContainer">
                @foreach ($project->subProjects as $idx => $sub)
                <div class="p-5 rounded-2xl bg-purple-50/40 border border-purple-200 subproject-card space-y-3">
                    <input type="hidden" name="sub_projects[{{ $idx }}][id]" value="{{ $sub->intSubProject_ID }}">
                    <div class="flex items-center justify-between pb-2 border-b border-purple-100">
                        <span class="text-xs font-black text-purple-900 flex items-center gap-2">
                            <i class="fa-solid fa-cube"></i> {{ $sub->txtSubProjectName }}
                        </span>
                        <button type="button" onclick="this.closest('.subproject-card').remove()" class="text-red-400 hover:text-red-600 text-xs"><i class="fa-solid fa-trash"></i> Hapus Sub Project</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-gray-600 uppercase">Nama Sub Project</label>
                            <input type="text" name="sub_projects[{{ $idx }}][name]" value="{{ $sub->txtSubProjectName }}" class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 uppercase">Bobot Sub (%)</label>
                            <input type="number" step="1" name="sub_projects[{{ $idx }}][weight]" value="{{ $sub->floatWeight }}" class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs text-center">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 uppercase">Score (1-5)</label>
                            <input type="number" min="1" max="5" name="sub_projects[{{ $idx }}][score]" value="{{ $sub->intScore }}" class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs text-center">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
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

@push('scripts')
<script>
    let stageIndex = {
        {
            $project - > directStages - > count() + 1
        }
    };
    let subIndex = {
        {
            $project - > subProjects - > count() + 1
        }
    };

    function toggleProjectStructure(hasSub) {
        const singleSec = document.getElementById('sectionSingleStages');
        const multiSec = document.getElementById('sectionMultiSubProjects');
        if (hasSub) {
            singleSec.classList.add('hidden');
            multiSec.classList.remove('hidden');
        } else {
            singleSec.classList.remove('hidden');
            multiSec.classList.add('hidden');
        }
    }

    function addSingleStageRow() {
        const tbody = document.getElementById('singleStagesBody');
        const tr = document.createElement('tr');
        tr.className = 'stage-row';
        tr.innerHTML = `
        <td class="p-2"><input type="text" name="stages[${stageIndex}][step]" placeholder="Nama Tahapan" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
        <td class="p-2"><input type="date" name="stages[${stageIndex}][start]" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
        <td class="p-2"><input type="date" name="stages[${stageIndex}][end]" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs"></td>
        <td class="p-2"><input type="number" step="1" name="stages[${stageIndex}][plan]" value="25" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
        <td class="p-2"><input type="number" step="1" name="stages[${stageIndex}][actual]" value="0" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 text-xs text-center"></td>
        <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-trash"></i></button></td>
    `;
        tbody.appendChild(tr);
        stageIndex++;
    }

    function addSubProjectCard() {
        const container = document.getElementById('subProjectsContainer');
        const div = document.createElement('div');
        div.className = 'p-5 rounded-2xl bg-purple-50/40 border border-purple-200 subproject-card space-y-3';
        div.innerHTML = `
        <div class="flex items-center justify-between pb-2 border-b border-purple-100">
            <span class="text-xs font-black text-purple-900 flex items-center gap-2">
                <i class="fa-solid fa-cube"></i> Sub Project Baru
            </span>
            <button type="button" onclick="this.closest('.subproject-card').remove()" class="text-red-400 hover:text-red-600 text-xs"><i class="fa-solid fa-trash"></i> Hapus Sub Project</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold text-gray-600 uppercase">Nama Sub Project</label>
                <input type="text" name="sub_projects[${subIndex}][name]" placeholder="Nama sub project..." class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-600 uppercase">Bobot Sub (%)</label>
                <input type="number" step="1" name="sub_projects[${subIndex}][weight]" value="30" class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs text-center">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-600 uppercase">Score (1-5)</label>
                <input type="number" min="1" max="5" name="sub_projects[${subIndex}][score]" value="3" class="w-full px-3 py-1.5 rounded-xl border border-gray-300 text-xs text-center">
            </div>
        </div>
    `;
        container.appendChild(div);
        subIndex++;
    }
</script>
@endpush
@endsection