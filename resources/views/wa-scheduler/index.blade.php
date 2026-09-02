@extends('layouts.app', [
'title' => 'WA Scheduler - KMI Activity Plan',
'pageTitle' => 'WHATSAPP SCHEDULER & BROADCAST',
'pageSubtitle' => '<span>Automated Activity & Daily Plan Reminders</span> &bull; <span>Superadmin Portal</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Jadwal Pengingat WhatsApp</h2>
            <p class="text-xs text-gray-500 m-0">Atur pengiriman pesan otomatis (misal setiap hari Jumat pukul 07:00) ke nomor WhatsApp employee.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" onclick="openTestModal()" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#006838] font-bold text-xs border border-emerald-200 transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Pesan Uji Coba</span>
            </button>
            <button type="button" onclick="openAddScheduleModal()" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Jadwal WA</span>
            </button>
        </div>
    </div>

    <!-- Active Schedules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse ($schedules as $sched)
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-500 text-xl"></i>
                        <h3 class="font-extrabold text-gray-900 text-base m-0">{{ $sched->txtScheduleTitle }}</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $sched->bitIsActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $sched->bitIsActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100 text-xs font-mono text-gray-800 whitespace-pre-line leading-relaxed">
                    {{ $sched->txtMessageTemplate }}
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 pt-1">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Jadwal Pengiriman</span>
                        <strong class="text-gray-900">{{ $sched->txtCronExpression }} ({{ $sched->txtScheduledTime }})</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Target Penerima</span>
                        <strong class="text-[#006838]">{{ $sched->txtTargetRole }}</strong>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <form action="{{ route('wa-scheduler.destroy', $sched) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xs p-1"><i class="fa-solid fa-trash"></i></button>
                </form>

                <form action="{{ route('wa-scheduler.trigger', $sched) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Broadcast Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-8 rounded-3xl border border-[#DDE5DD] text-center text-gray-400 space-y-2">
            <i class="fa-brands fa-whatsapp text-3xl text-emerald-400"></i>
            <p class="text-xs text-gray-600">Belum ada konfigurasi jadwal WhatsApp.</p>
        </div>
        @endforelse
    </div>

    <!-- WhatsApp Broadcast Logs Table -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0">Riwayat Pengiriman Pesan (Broadcast Logs)</h3>
                <p class="text-xs text-gray-500 m-0">Log status pengiriman API WhatsApp Gateway.</p>
            </div>
            <span class="text-xs font-bold text-gray-400">{{ $logs->count() }} log terakhir</span>
        </div>

        <div class="overflow-x-auto max-h-96 custom-scrollbar">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase sticky top-0">
                    <tr>
                        <th class="p-3">Waktu Kirim</th>
                        <th class="p-3">Penerima & Nomor</th>
                        <th class="p-3">Isi Pesan</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 font-mono text-gray-500 whitespace-nowrap">{{ $log->dtmSentAt?->format('d M Y H:i:s') }}</td>
                        <td class="p-3">
                            <span class="font-bold text-gray-800 block">{{ $log->user?->txtEmployeeName ?? 'General' }}</span>
                            <span class="text-[11px] text-emerald-700 font-mono"><i class="fa-brands fa-whatsapp"></i> {{ $log->txtRecipientPhone }}</span>
                        </td>
                        <td class="p-3 max-w-md">
                            <p class="line-clamp-2 text-gray-600 m-0">{{ $log->txtMessageContent }}</p>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->txtStatus === 'Success' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $log->txtStatus }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400">Belum ada catatan log pengiriman WA.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Add Schedule -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="addScheduleModal">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Jadwal WA Pengingat</h3>
            <button type="button" onclick="closeModal('addScheduleModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('wa-scheduler.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Jadwal</label>
                <input type="text" name="txtScheduleTitle" value="Pengingat Daily Plan & KPI Jumat Pagi" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cron Expression / Hari</label>
                    <input type="text" name="txtCronExpression" value="Every Friday" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jam Kirim</label>
                    <input type="text" name="txtScheduledTime" value="07:00" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Penerima</label>
                <select name="txtTargetRole" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
                    <option value="All">Semua Karyawan</option>
                    <option value="Employee">Employee Only</option>
                    <option value="Supervisor">Supervisor Only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Template Pesan (Gunakan tag {employee_name} & {department})</label>
                <textarea name="txtMessageTemplate" rows="4" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none font-mono">Halo {employee_name}, selamat pagi! Jangan lupa untuk memperbarui Daily Task dan Daily Plan aktivitas mingguan Anda di KMI Activity Plan. Terima kasih! - {department}</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('addScheduleModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] text-white text-xs font-bold">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Test Send -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="testSendModal">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="text-base font-extrabold text-gray-900 m-0">Uji Coba Pengiriman WA</h3>
            <button type="button" onclick="closeModal('testSendModal')" class="text-gray-400 p-1"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('wa-scheduler.test-send') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor WhatsApp Tujuan</label>
                <input type="text" name="phone" value="{{ $authUser->txtPhone ?? '6281234567890' }}" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pesan Uji Coba</label>
                <textarea name="message" rows="3" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">Halo! Ini adalah pesan uji coba dari sistem KMI Activity Plan Kalbe Nutritionals.</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('testSendModal')" class="px-4 py-2 rounded-xl bg-gray-100 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">Kirim Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddScheduleModal() {
        document.getElementById('addScheduleModal').classList.remove('hidden');
    }

    function openTestModal() {
        document.getElementById('testSendModal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection