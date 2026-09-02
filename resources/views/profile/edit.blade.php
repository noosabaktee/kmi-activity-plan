@extends('layouts.app', [
    'title' => 'Edit Profile - KMI Activity Plan',
    'pageTitle' => 'EDIT PROFILE',
    'pageSubtitle' => '<span>' . $user->txtEmployeeName . '</span>',
])

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h2 class="text-xl font-black text-gray-900 tracking-tight m-0">Edit Data Profile</h2>
        <a href="{{ route('profile.show') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition flex items-center gap-1.5 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" class="bg-white p-6 md:p-8 rounded-3xl border border-[#DDE5DD] shadow-xs space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
            <input type="text" name="txtEmployeeName" value="{{ old('txtEmployeeName', $user->txtEmployeeName) }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">NIK / Kode Karyawan</label>
            <input type="text" name="txtEmployeeCode" value="{{ old('txtEmployeeCode', $user->txtEmployeeCode) }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email (Read-only)</label>
            <input type="email" value="{{ $user->txtEmail }}" disabled
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 text-xs cursor-not-allowed">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor WhatsApp</label>
            <input type="text" name="txtPhone" value="{{ old('txtPhone', $user->txtPhone) }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                placeholder="628123456789">
        </div>

        <div class="pt-2 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-700 uppercase mb-2">Ganti Kata Sandi (Opsional)</h4>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Kata Sandi Baru</label>
                    <input type="password" name="password"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                        placeholder="Kosongkan jika tidak diubah">
                </div>

                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] outline-none"
                        placeholder="Ulangi kata sandi baru">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-4">
            <a href="{{ route('profile.show') }}" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold text-xs no-underline">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition">
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
