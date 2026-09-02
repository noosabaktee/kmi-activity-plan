@extends('layouts.app', [
'title' => 'Daily Plan (Weekly) - KMI Activity Plan',
'pageTitle' => 'DAILY PLAN (WEEKLY FORMAT)',
'pageSubtitle' => '<span>Rencana Kerja Mingguan</span> &bull; <span>Jadwal Aktivitas Senin - Jumat</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header Actions Toolbar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Kartu Rencana Mingguan (Weekly Plan)</h2>
            <p class="text-xs text-gray-500 m-0">Pilih kartu minggu untuk membuka dan mengisi jadwal aktivitas harian Senin s/d Jumat.</p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" onclick="openNewWeeklyModal()" class="px-4 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Weekly Plan Card</span>
            </button>
        </div>
    </div>

    <!-- Filter Employee (if Admin / SPV / Head) -->
    @if (! $authUser->isEmployee())
    <div class="bg-white p-4 rounded-2xl border border-[#DDE5DD] shadow-xs flex items-center justify-between">
        <form method="GET" action="{{ route('reports.daily-plans') }}" class="flex items-center gap-3 w-full sm:w-auto">
            <label class="text-xs font-bold text-gray-600 shrink-0">Filter Employee:</label>
            <select name="employee" onchange="this.form.submit()" class="px-3.5 py-1.5 rounded-xl border border-gray-200 text-xs focus:border-[#006838] outline-none">
                <option value="">Semua Employee</option>
                @foreach ($employees as $emp)
                <option value="{{ $emp->intUser_ID }}" {{ request('employee') == $emp->intUser_ID ? 'selected' : '' }}>
                    {{ $emp->txtEmployeeName }} ({{ $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    <!-- Weekly Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($weeklyPlans as $plan)
        <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs hover:shadow-md hover:border-emerald-300 transition flex flex-col justify-between overflow-hidden group">
            <div class="p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-[#006838] font-black text-xs border border-emerald-200">
                        <i class="fa-solid fa-calendar-days mr-1"></i> {{ $plan->txtWeekTitle }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $plan->txtStatus === 'Approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $plan->txtStatus }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-gray-900 group-hover:text-[#006838] transition m-0">
                        {{ $plan->dtmWeekStartDate?->format('d M') }} - {{ $plan->dtmWeekEndDate?->format('d M Y') }}
                    </h3>
                    <p class="text-xs text-gray-500 font-medium m-0 mt-1">
                        PIC: <strong class="text-gray-800">{{ $plan->user?->txtEmployeeName ?? '-' }}</strong> ({{ $plan->user?->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                    </p>
                </div>

                @if ($plan->txtTargetGoals)
                <p class="text-xs text-gray-600 bg-gray-50 p-3 rounded-2xl border border-gray-100 line-clamp-2">
                    {{ $plan->txtTargetGoals }}
                </p>
                @endif

                <div class="pt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>Total Jadwal Aktivitas:</span>
                    <strong class="text-[#006838] font-black text-sm">{{ $plan->activities->count() }} Kegiatan</strong>
                </div>
            </div>

            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <form action="{{ route('daily-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Hapus Weekly Card ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xs p-1 transition" title="Hapus Card">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </form>

                <a href="{{ route('daily-plans.show', $plan) }}" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 no-underline">
                    <span>Buka Jadwal Harian</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-12 rounded-3xl border border-[#DDE5DD] text-center text-gray-400 space-y-3">
            <i class="fa-solid fa-calendar-plus text-4xl text-gray-300"></i>
            <h3 class="text-base font-bold text-gray-700 m-0">Belum Ada Weekly Plan</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto">Buat kartu mingguan baru untuk mulai merencanakan aktivitas harian Senin s/d Jumat.</p>
            <button type="button" onclick="openNewWeeklyModal()" class="px-4 py-2 rounded-xl bg-[#006838] text-white font-bold text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Weekly Plan Baru</span>
            </button>
        </div>
        @endforelse
    </div>

</div>

<!-- Modal Create Weekly Plan -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="newWeeklyModal">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100 animate-scale-in">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-extrabold text-gray-900 m-0 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-[#006838]"></i>
                <span>Tambah Weekly Plan Card</span>
            </h3>
            <button type="button" onclick="closeNewWeeklyModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('daily-plans.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Judul Week Card <span class="text-red-500">*</span></label>
                <input type="text" name="txtWeekTitle" value="Week 1: {{ date('d M') }} - {{ date('d M Y', strtotime('+4 days')) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Start (Senin) <span class="text-red-500">*</span></label>
                    <input type="date" name="dtmWeekStartDate" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">End (Jumat) <span class="text-red-500">*</span></label>
                    <input type="date" name="dtmWeekEndDate" value="{{ date('Y-m-d', strtotime('+4 days')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
            </div>

            @if (! $authUser->isEmployee())
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Employee PIC</label>
                <select name="intUser_ID" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    @foreach ($employees as $emp)
                    <option value="{{ $emp->intUser_ID }}" {{ $authUser->intUser_ID == $emp->intUser_ID ? 'selected' : '' }}>
                        {{ $emp->txtEmployeeName }} ({{ $emp->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Target / Sasaran Mingguan</label>
                <textarea name="txtTargetGoals" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                    placeholder="Contoh: Selesaikan commissioning sensor ampere & presentasi weekly meeting..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeNewWeeklyModal()" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold text-xs">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition">Simpan Card</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewWeeklyModal() {
        document.getElementById('newWeeklyModal').classList.remove('hidden');
    }

    function closeNewWeeklyModal() {
        document.getElementById('newWeeklyModal').classList.add('hidden');
    }
</script>
@endsection