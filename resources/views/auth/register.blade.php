<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - KMI Activity Plan | Kalbe Nutritionals</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7F4] text-[#222222] font-['Inter',sans-serif] min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-[#DDE5DD] p-8 md:p-10 my-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
            <div class="w-10 h-10 rounded-xl bg-[#006838] text-white flex items-center justify-center font-extrabold text-lg shadow">
                K
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 m-0">Daftar Akun KMI Activity Plan</h1>
                <p class="text-xs text-gray-500 m-0">Registrasi akun karyawan / supervisor / head untuk departemen Anda.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="txtEmployeeName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" id="txtEmployeeName" name="txtEmployeeName" value="{{ old('txtEmployeeName') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="Contoh: Anthony Wijaya">
                </div>

                <div>
                    <label for="txtEmployeeCode" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kode / NIK (Opsional)</label>
                    <input type="text" id="txtEmployeeCode" name="txtEmployeeCode" value="{{ old('txtEmployeeCode') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="Contoh: AHO / EMP-102">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="txtEmail" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Kalbe</label>
                    <input type="email" id="txtEmail" name="txtEmail" value="{{ old('txtEmail') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="name@kalbe.co.id">
                </div>

                <div>
                    <label for="txtPhone" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor WhatsApp</label>
                    <input type="text" id="txtPhone" name="txtPhone" value="{{ old('txtPhone') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="Contoh: 6281234567890">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="intDepartment_ID" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Department</label>
                    <select id="intDepartment_ID" name="intDepartment_ID" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->intDepartment_ID }}" {{ old('intDepartment_ID') == $dept->intDepartment_ID ? 'selected' : '' }}>
                                {{ $dept->txtDepartmentCode }} - {{ $dept->txtDepartmentName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="intSubDepartment_ID" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Sub Department</label>
                    <select id="intSubDepartment_ID" name="intSubDepartment_ID"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition">
                        <option value="">Pilih Sub Department</option>
                        @foreach ($subDepartments as $sd)
                            <option value="{{ $sd->intSubDepartment_ID }}" {{ old('intSubDepartment_ID') == $sd->intSubDepartment_ID ? 'selected' : '' }}>
                                {{ $sd->txtSubDepartmentCode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="txtRole" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role / Level</label>
                    <select id="txtRole" name="txtRole" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition">
                        <option value="Employee" {{ old('txtRole') == 'Employee' ? 'selected' : '' }}>Employee</option>
                        <option value="Supervisor" {{ old('txtRole') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="Head" {{ old('txtRole') == 'Head' ? 'selected' : '' }}>Head</option>
                        <option value="Superadmin" {{ old('txtRole') == 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="txtPassword" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                    <input type="password" id="txtPassword" name="txtPassword" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label for="txtPassword_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi</label>
                    <input type="password" id="txtPassword_confirmation" name="txtPassword_confirmation" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="Ulangi kata sandi">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>Daftar Akun</span>
                    <i class="fa-solid fa-user-plus text-xs"></i>
                </button>
            </div>

            <div class="text-center pt-2 text-xs text-gray-600">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-[#006838] hover:underline">Masuk di sini</a>
            </div>
        </form>
    </div>
</body>
</html>
