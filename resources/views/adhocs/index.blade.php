@extends('layouts.app', [
'title' => 'Daftar Ad Hoc - KMI Activity Plan',
'pageTitle' => 'AD HOC INITIATIVE MANAGEMENT',
'pageSubtitle' => '<span>Penanganan Sasaran Khusus & Sifat Sementara</span> &bull; <span>Gugus Tugas & Action Plan</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight m-0">Katalog Inisiatif & Kegiatan Ad Hoc</h2>
            <p class="text-xs text-gray-500 m-0">Kelola rencana inisiatif ad hoc yang dibentuk secara khusus untuk menangani situasi sementara atau sasaran tertentu.</p>
        </div>
        <a href="{{ route('adhocs.create') }}" class="px-4 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 no-underline shrink-0" style="background-color: #006838; color: #ffffff;">
            <i class="fa-solid fa-plus text-white"></i>
            <span>Buat Ad Hoc Baru</span>
        </a>
    </div>

    <!-- Summary Metrics Bar (Strictly 1 Single Row) -->
    <div class="grid grid-cols-5 gap-3">
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs min-w-0">
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider block truncate">Total Ad Hoc</span>
            <div class="text-lg sm:text-xl font-black text-[#006838] mt-0.5">{{ $summary['total'] }}</div>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs min-w-0">
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider block truncate">Sedang Ditangani</span>
            <div class="text-lg sm:text-xl font-black text-blue-600 mt-0.5">{{ $summary['inProgress'] }}</div>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs min-w-0">
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider block truncate">Selesai Ditangani</span>
            <div class="text-lg sm:text-xl font-black text-emerald-600 mt-0.5">{{ $summary['completed'] }}</div>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs min-w-0">
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider block truncate">Kritis / Urgent</span>
            <div class="text-lg sm:text-xl font-black text-rose-600 mt-0.5">{{ $summary['critical'] }}</div>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#DDE5DD] shadow-2xs min-w-0">
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider block truncate">Rata-rata Progres</span>
            <div class="text-lg sm:text-xl font-black text-[#8CC63F] mt-0.5">{{ $summary['avgProgress'] }}%</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar (Without Sub Dept & PIC) -->
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs">
        <form method="GET" action="{{ route('adhocs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Kategori Ad Hoc</label>
                <select name="category" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach (['Troubleshooting & Problem Solving', 'Special Request Management', 'Emergency Response', 'Audit & Compliance Finding', 'Process Improvement / Kaizen', 'Task Force Khusus'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tingkat Urgensi</label>
                <select name="urgency" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Urgensi</option>
                    <option value="Critical" {{ request('urgency') == 'Critical' ? 'selected' : '' }}>Critical (Kritis)</option>
                    <option value="High" {{ request('urgency') == 'High' ? 'selected' : '' }}>High (Tinggi)</option>
                    <option value="Medium" {{ request('urgency') == 'Medium' ? 'selected' : '' }}>Medium (Normal)</option>
                    <option value="Low" {{ request('urgency') == 'Low' ? 'selected' : '' }}>Low (Rendah)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                    <option value="">Semua Status</option>
                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Under Review" {{ request('status') == 'Under Review' ? 'selected' : '' }}>Under Review</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cari Ad Hoc</label>
                <div class="flex items-center gap-1.5">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari inisiatif..."
                            class="w-full pl-8 pr-3 py-2 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none bg-white">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                    @if (request()->hasAny(['category', 'urgency', 'status', 'search']))
                    <a href="{{ route('adhocs.index') }}" class="px-2.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition flex items-center gap-1 shrink-0 no-underline" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset</span>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Ad Hoc Table -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5 min-w-[240px]">Inisiatif Ad Hoc & Sasaran Khusus</th>
                        <th class="px-4 py-3.5 w-44">Kategori & Urgensi</th>
                        <th class="px-4 py-3.5 w-44">Sifat Sementara (Periode)</th>
                        <th class="px-4 py-3.5 w-48">PIC & Tim Pelaksana</th>
                        <th class="px-4 py-3.5 w-36 text-center">Tahapan / Progres</th>
                        <th class="px-4 py-3.5 w-28 text-center">Status</th>
                        <th class="px-4 py-3.5 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($adhocs as $index => $adhoc)
                    @php
                    $urgency = $adhoc->txtPriority ?: 'Medium';
                    $urgencyClasses = match($urgency) {
                    'Critical' => 'bg-red-100 text-red-700 border-red-200',
                    'High' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'Medium' => 'bg-blue-100 text-blue-800 border-blue-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                    };
                    $urgencyIcon = match($urgency) {
                    'Critical' => 'fa-solid fa-fire',
                    'High' => 'fa-solid fa-triangle-exclamation',
                    'Medium' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-circle-info',
                    };

                    $startDate = $adhoc->dtmProjectStartDate;
                    $endDate = $adhoc->dtmProjectEndDate;
                    $daysDuration = $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : null;
                    $isExpired = $endDate && $endDate->isPast() && $adhoc->floatActual < 100;
                        @endphp
                        <tr class="hover:bg-emerald-50/20 transition group">
                        <td class="px-4 py-3.5 text-center text-gray-400 font-bold">
                            {{ $index + 1 }}
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <a href="{{ route('adhocs.show', $adhoc) }}" class="font-extrabold text-gray-900 group-hover:text-[#006838] transition no-underline text-xs line-clamp-1 block">
                                    {{ $adhoc->txtProjectName }}
                                </a>
                                @if ($adhoc->txtSpecialGoal)
                                <p class="text-[11px] text-gray-500 m-0 line-clamp-1 italic">
                                    <i class="fa-solid fa-bullseye text-[#006838] mr-1"></i>{{ $adhoc->txtSpecialGoal }}
                                </p>
                                @endif
                                @if ($adhoc->txtDeliverable)
                                <div class="text-[10px] text-emerald-800 font-medium">
                                    <strong>Output:</strong> {{ $adhoc->txtDeliverable }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3.5 space-y-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $urgencyClasses }}">
                                <i class="{{ $urgencyIcon }} text-[9px]"></i>
                                <span>{{ $urgency }}</span>
                            </span>
                            <div class="text-[10px] text-gray-600 font-semibold truncate" title="{{ $adhoc->txtAdHocCategory }}">
                                {{ $adhoc->txtAdHocCategory ?: 'Ad Hoc Initiative' }}
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="text-[11px] font-bold text-gray-800">
                                {{ $startDate ? $startDate->format('d M') : '-' }} s/d {{ $endDate ? $endDate->format('d M Y') : '-' }}
                            </div>
                            <div class="mt-1 flex items-center gap-1.5">
                                @if ($daysDuration)
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold">
                                    <i class="fa-regular fa-clock text-[9px] mr-1"></i>{{ $daysDuration }} Hari
                                </span>
                                @endif
                                @if ($isExpired)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 text-rose-700">
                                    Lewat Batas
                                </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#006838] text-white flex items-center justify-center font-bold text-[10px] shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($adhoc->user?->txtEmployeeName ?? 'U', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 truncate m-0 text-xs">{{ $adhoc->user?->txtEmployeeName ?? '-' }}</p>
                                    <p class="text-[10px] text-gray-400 m-0 truncate">{{ $adhoc->subDepartment?->txtSubDepartmentCode ?? 'MDP' }}</p>
                                </div>
                            </div>
                            @if ($adhoc->assignments->count() > 0)
                            <div class="mt-1.5 flex items-center gap-1 text-[10px] text-emerald-800 font-semibold">
                                <i class="fa-solid fa-users text-[9px]"></i>
                                <span>+{{ $adhoc->assignments->count() }} Anggota Tim</span>
                            </div>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <div class="space-y-1">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#006838] h-2 rounded-full transition-all" style="width: {{ min(100, $adhoc->floatActual) }}%"></div>
                                    </div>
                                    <span class="font-extrabold text-[11px] text-gray-800 tabular-nums">{{ round($adhoc->floatActual) }}%</span>
                                </div>
                                <span class="text-[10px] text-gray-400 block font-medium">
                                    {{ $adhoc->directStages->count() }} Tahap Aksi
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            @php
                            $status = $adhoc->txtStatus ?: 'In Progress';
                            $statusClass = match(strtolower($status)) {
                            'completed', 'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'under review' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            default => 'bg-emerald-50 text-[#006838] border-emerald-200',
                            };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('adhocs.show', $adhoc) }}" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-[#006838] flex items-center justify-center transition border border-emerald-200 shadow-2xs" title="Lihat Detail">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('adhocs.edit', $adhoc) }}" class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 flex items-center justify-center transition border border-blue-200 shadow-2xs" title="Edit Ad Hoc">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                                <form action="{{ route('adhocs.destroy', $adhoc) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan inisiatif Ad Hoc ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition border border-rose-200 shadow-2xs cursor-pointer" title="Hapus Ad Hoc">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#006838] flex items-center justify-center mx-auto text-xl shadow-xs">
                                        <i class="fa-solid fa-bolt"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-800 m-0">Belum Ada Inisiatif Ad Hoc</h4>
                                    <p class="text-xs text-gray-500 m-0">Inisiatif ad hoc dibentuk untuk menangani sasaran atau situasi tertentu yang sifatnya sementara.</p>
                                    <a href="{{ route('adhocs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition no-underline" style="background-color: #006838; color: #ffffff;">
                                        <i class="fa-solid fa-plus text-white"></i>
                                        <span>Buat Inisiatif Ad Hoc Pertama</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($adhocs, 'hasPages') && $adhocs->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $adhocs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection