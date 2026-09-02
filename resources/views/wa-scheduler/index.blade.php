@extends('layouts.app', [
'title' => 'WA Scheduler & Settings - KMI Activity Plan',
'pageTitle' => 'WHATSAPP SCHEDULER & BROADCAST',
'pageSubtitle' => '<span>Automated Activity & Daily Plan Reminders</span> &bull; <span>Superadmin Portal</span>',
])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Jadwal Pengingat WhatsApp</h2>
            <p class="text-xs text-gray-500 m-0">Atur pengiriman pesan otomatis berkala ke nomor WhatsApp employee berdasarkan hari & department.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <button type="button" onclick="openSettingsModal()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs border border-gray-300 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-sliders text-[#006838]"></i>
                <span>Pengaturan API WA</span>
            </button>
            <button type="button" onclick="openTestModal()" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#006838] font-bold text-xs border border-emerald-200 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Pesan Uji Coba</span>
            </button>
            <button type="button" onclick="openAddScheduleModal()" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Jadwal WA</span>
            </button>
        </div>
    </div>

    <!-- API Config Active Banner -->
    <!-- <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-900 via-[#006838] to-emerald-800 text-white shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-xl text-emerald-300 shrink-0">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">WhatsApp Gateway Connected</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-xs text-white/90 flex flex-wrap items-center gap-x-4 gap-y-1 mt-0.5 font-mono">
                    <span><strong>Sender:</strong> {{ $settings['wa_sender'] ?? 'Not Configured' }}</span>
                    <span><strong>API Key:</strong> {{ !empty($settings['wa_api_key']) ? substr($settings['wa_api_key'], 0, 6) . '••••••' : '-' }}</span>
                    <span><strong>Endpoint:</strong> {{ Str::limit($settings['wa_api_url'] ?? '', 35) }}</span>
                </div>
            </div>
        </div>
        <button type="button" onclick="openSettingsModal()" class="px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shrink-0">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Ubah Setting API</span>
        </button>
    </div> -->

    <!-- Active Schedules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse ($schedules as $sched)
        <div class="bg-white p-6 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4 flex flex-col justify-between hover:border-emerald-300 transition">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-[#006838] flex items-center justify-center font-bold text-sm">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-gray-900 text-sm m-0">{{ $sched->txtScheduleTitle }}</h3>
                            <span class="text-[10px] text-gray-400 font-medium">ID: #SCHED-{{ $sched->intWaSchedule_ID }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $sched->bitActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $sched->bitActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-100 text-xs font-mono text-gray-800 whitespace-pre-line leading-relaxed">
                    {{ $sched->txtMessageTemplate }}
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600 pt-1">
                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 space-y-0.5">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
                            <i class="fa-regular fa-calendar-days text-emerald-600"></i> Hari & Waktu
                        </span>
                        <strong class="text-gray-900 block text-xs">
                            {{ $sched->txtCronDay ?: $sched->txtCronExpression }}
                        </strong>
                        <span class="text-[11px] text-emerald-700 font-bold">Pukul {{ $sched->txtScheduledTime }} WIB</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 space-y-0.5">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
                            <i class="fa-solid fa-users text-blue-600"></i> Target Penerima
                        </span>
                        <strong class="text-[#006838] block text-xs truncate">
                            @if ($sched->user)
                            {{ $sched->user->txtEmployeeName }}
                            @elseif ($sched->txtTargetRole)
                            {{ $sched->txtTargetRole }}
                            @else
                            Semua Karyawan
                            @endif
                        </strong>
                        <span class="text-[11px] text-gray-500 block truncate">
                            Dept: {{ $sched->department?->txtDepartmentName ?? 'Semua Dept' }}
                        </span>
                    </div>
                </div>

                @if ($sched->dtmLastSentAt)
                <div class="text-[10px] text-gray-400 flex items-center gap-1">
                    <i class="fa-regular fa-clock"></i>
                    <span>Terakhir dikirim: {{ $sched->dtmLastSentAt->translatedFormat('d M Y H:i') }}</span>
                </div>
                @endif
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <form action="{{ route('wa-scheduler.destroy', $sched) }}" method="POST" onsubmit="return confirm('Hapus jadwal pengingat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xs p-1 cursor-pointer transition flex items-center gap-1">
                        <i class="fa-solid fa-trash"></i>
                        <span>Hapus</span>
                    </button>
                </form>

                <form action="{{ route('wa-scheduler.trigger', $sched) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Broadcast Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-12 rounded-3xl border border-[#DDE5DD] text-center text-gray-400 space-y-3">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 mx-auto flex items-center justify-center text-2xl">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 text-sm m-0">Belum Ada Jadwal WhatsApp</h4>
                <p class="text-xs text-gray-500 mt-1 m-0">Buat jadwal baru untuk mengirim pengingat otomatis ke tim Anda.</p>
            </div>
            <button type="button" onclick="openAddScheduleModal()" class="px-4 py-2 rounded-xl bg-[#006838] text-white font-bold text-xs">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Jadwal Sekarang
            </button>
        </div>
        @endforelse
    </div>

    <!-- WhatsApp Broadcast Logs Table -->
    <div class="bg-white rounded-3xl border border-[#DDE5DD] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#006838]"></i>
                    <span>Riwayat Pengiriman Pesan (Broadcast Logs)</span>
                </h3>
                <p class="text-xs text-gray-500 m-0">Log status pengiriman API WhatsApp Gateway real-time.</p>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full">{{ $logs->count() }} log terakhir</span>
        </div>

        <div class="overflow-x-auto max-h-96 custom-scrollbar">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase sticky top-0 border-b border-gray-200">
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
                            <span class="font-bold text-gray-800 block">{{ $log->txtRecipientName ?: ($log->user?->txtEmployeeName ?? 'General') }}</span>
                            <span class="text-[11px] text-emerald-700 font-mono"><i class="fa-brands fa-whatsapp"></i> {{ $log->txtRecipientPhone }}</span>
                        </td>
                        <td class="p-3 max-w-md">
                            <p class="line-clamp-2 text-gray-600 m-0 font-sans">{{ $log->txtMessage }}</p>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ strtolower($log->txtStatus) === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                <i class="fa-solid {{ strtolower($log->txtStatus) === 'success' ? 'fa-check' : 'fa-xmark' }} mr-1"></i>
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
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl animate-fade-in max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-[#006838] flex items-center justify-center font-bold">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Tambah Jadwal WA Pengingat</h3>
                    <p class="text-xs text-gray-500 m-0">Konfigurasi hari, target penerima, dan pesan</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('addScheduleModal')" class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form action="{{ route('wa-scheduler.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Judul Jadwal <span class="text-red-500">*</span></label>
                <input type="text" name="txtScheduleTitle" value="Pengingat Daily Plan & KPI Jumat Pagi" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none"
                    placeholder="Contoh: Pengingat Update Daily Task">
            </div>

            <!-- Input Hari & Jam -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pilih Hari Pengiriman <span class="text-red-500">*</span></label>
                    <select name="txtCronDay" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none font-semibold text-gray-800">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat" selected>Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                        <option value="Setiap Hari">Setiap Hari (Senin - Minggu)</option>
                        <option value="Hari Kerja (Senin - Jumat)">Hari Kerja (Senin - Jumat)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Jam Pengiriman <span class="text-red-500">*</span></label>
                    <input type="time" name="txtScheduledTime" value="07:00" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none font-semibold">
                </div>
            </div>

            <!-- Two Target Inputs: 1. Department, 2. Target Penerima -->
            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 space-y-3">
                <div class="font-extrabold text-xs text-[#006838] uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-bullseye"></i>
                    <span>Target Penerima Pesan</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Target 1: Department Selector -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">1. Pilih Department</label>
                        <select name="intDepartment_ID" id="addSchedDept" onchange="filterUsersByDept(this.value)" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none bg-white font-medium">
                            <option value="">Semua Department</option>
                            @foreach ($departments as $dept)
                            <option value="{{ $dept->intDepartment_ID }}">
                                {{ $dept->txtDepartmentCode }} - {{ $dept->txtDepartmentName }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target 2: Recipient Selector -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">2. Target Penerima</label>
                        <select name="txtTargetRecipient" id="addSchedRecipient" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none bg-white font-medium">
                            <optgroup label="Berdasarkan Role / Kelompok">
                                <option value="All" selected>Semua Karyawan (All)</option>
                                <option value="Employee">Employee / Intern Saja</option>
                                <option value="Supervisor">Supervisor & Head Saja</option>
                            </optgroup>
                            <optgroup label="Spesifik Karyawan / PIC" id="recipientUserGroup">
                                @foreach ($users as $u)
                                <option value="USER_{{ $u->intUser_ID }}" data-dept="{{ $u->intDepartment_ID }}">
                                    {{ $u->txtEmployeeName }} ({{ $u->txtRole }} - {{ $u->subDepartment?->txtSubDepartmentCode ?? 'MDP' }})
                                </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Template Pesan -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Template Pesan <span class="text-red-500">*</span></label>
                    <span class="text-[10px] text-gray-400">Klik tag untuk menyisipkan:</span>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-2">
                    <button type="button" onclick="insertTag('{employee_name}')" class="px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-emerald-100 text-[10px] font-mono font-bold text-gray-700 transition cursor-pointer">{employee_name}</button>
                    <button type="button" onclick="insertTag('{department}')" class="px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-emerald-100 text-[10px] font-mono font-bold text-gray-700 transition cursor-pointer">{department}</button>
                    <button type="button" onclick="insertTag('{subdept}')" class="px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-emerald-100 text-[10px] font-mono font-bold text-gray-700 transition cursor-pointer">{subdept}</button>
                    <button type="button" onclick="insertTag('{date}')" class="px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-emerald-100 text-[10px] font-mono font-bold text-gray-700 transition cursor-pointer">{date}</button>
                    <button type="button" onclick="insertTag('{day}')" class="px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-emerald-100 text-[10px] font-mono font-bold text-gray-700 transition cursor-pointer">{day}</button>
                </div>
                <textarea name="txtMessageTemplate" id="txtMessageTemplate" rows="4" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none font-mono">Halo {employee_name}, selamat pagi! Jangan lupa untuk memperbarui Daily Task dan Daily Plan aktivitas mingguan Anda di KMI Activity Plan. Terima kasih! - {department}</textarea>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeModal('addScheduleModal')" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-gray-700 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Settings WhatsApp API -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="settingsModal">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Pengaturan API WhatsApp Gateway</h3>
                    <p class="text-xs text-gray-500 m-0">Konfigurasi kredensial API Key & Sender Number</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('settingsModal')" class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form action="{{ route('wa-scheduler.settings.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">WhatsApp API Endpoint URL <span class="text-red-500">*</span></label>
                <input type="url" name="wa_api_url" value="{{ $settings['wa_api_url'] }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none font-mono"
                    placeholder="https://whatsapp.intconnect.id/send-message">
                <span class="text-[10px] text-gray-400 mt-1 block">URL endpoint gateway API WhatsApp yang digunakan sistem.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">API Key <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" name="wa_api_key" id="waApiKeyInput" value="{{ $settings['wa_api_key'] }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none font-mono pr-10"
                        placeholder="Contoh: kmi_wa_api_key_xxxx">
                    <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sender Number / Device ID <span class="text-red-500">*</span></label>
                <input type="text" name="wa_sender" value="{{ $settings['wa_sender'] }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none font-mono"
                    placeholder="Contoh: 6281234567890">
                <span class="text-[10px] text-gray-400 mt-1 block">Nomor pengirim atau Session Device ID yang terdaftar di gateway.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Footer Text / Signature (Opsional)</label>
                <input type="text" name="wa_footer" value="{{ $settings['wa_footer'] }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none"
                    placeholder="Contoh: Sent via KMI Activity Plan Kalbe">
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeModal('settingsModal')" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-gray-700 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Pengaturan API</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Test Send -->
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden" id="testSendModal">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-[#006838] flex items-center justify-center font-bold">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 m-0">Uji Coba Pengiriman WA</h3>
                    <p class="text-xs text-gray-500 m-0">Kirim pesan uji coba ke nomor WhatsApp Anda</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('testSendModal')" class="text-gray-400 hover:text-gray-600 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form action="{{ route('wa-scheduler.test-send') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor WhatsApp Tujuan <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ $authUser->txtPhone ?? '6281234567890' }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none font-mono"
                    placeholder="628xxxxxxxxxx">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pesan Uji Coba <span class="text-red-500">*</span></label>
                <textarea name="message" rows="3" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none">Halo! Ini adalah pesan uji coba dari sistem KMI Activity Plan Kalbe Nutritionals. API WhatsApp Gateway berhasil terhubung.</textarea>
            </div>
            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeModal('testSendModal')" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-gray-700">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddScheduleModal() {
        document.getElementById('addScheduleModal').classList.remove('hidden');
    }

    function openSettingsModal() {
        document.getElementById('settingsModal').classList.remove('hidden');
    }

    function openTestModal() {
        document.getElementById('testSendModal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function insertTag(tag) {
        const textarea = document.getElementById('txtMessageTemplate');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    }

    function filterUsersByDept(deptId) {
        const userGroup = document.getElementById('recipientUserGroup');
        const options = userGroup.querySelectorAll('option');
        options.forEach(opt => {
            const optDept = opt.getAttribute('data-dept');
            if (!deptId || optDept === deptId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function toggleApiKeyVisibility() {
        const input = document.getElementById('waApiKeyInput');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection