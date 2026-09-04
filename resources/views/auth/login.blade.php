<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KMI Activity Plan | Kalbe Nutritionals</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F7F4] text-[#222222] font-['Inter',sans-serif] min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl border border-[#DDE5DD] overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">

        <!-- Left Side: Branding & Quick Stats -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#006838] via-[#004d29] to-[#00331b] p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/5 blur-2xl"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-[#8CC63F]/10 blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-lg p-2">
                        <img src="{{ asset('images/KDC.png') }}" alt="Kalbe" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fa-solid fa-leaf text-[#006838] text-2xl\'></i>'">
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight m-0 text-white">KALBE</h2>
                        <p class="text-xs text-emerald-300 font-semibold tracking-widest uppercase m-0">Nutritionals</p>
                    </div>
                </div>

                <div class="inline-block px-3 py-1 rounded-full bg-white/10 border border-white/20 text-emerald-300 text-xs font-semibold mb-4">
                    MDP Department Monitoring
                </div>

                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight leading-tight text-white mb-3">
                    KMI Activity Plan & KPI Monitoring
                </h1>
                <p class="text-sm text-emerald-100/90 leading-relaxed">
                    Sistem pemantauan rencana aktivitas kerja, exposure S-Curve, dan Key Performance Index (KPI) tahunan terpadu.
                </p>
            </div>

            <!-- Department KPI Summary Cards -->
            <div class="relative z-10 grid grid-cols-3 gap-2.5 my-6 py-4 border-y border-white/15 bg-white/5 rounded-2xl px-3">
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-extrabold text-[#8CC63F]">{{ $loginSummary['totalProjects'] }}</div>
                    <div class="text-[10px] text-emerald-200 uppercase font-medium">Projects</div>
                </div>
                <div class="text-center border-x border-white/15">
                    <div class="text-xl md:text-2xl font-extrabold text-white">{{ $loginSummary['avgActual'] }}%</div>
                    <div class="text-[10px] text-emerald-200 uppercase font-medium">Exposure Avg</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-extrabold text-[#8CC63F]">{{ $loginSummary['totalEmployees'] }}</div>
                    <div class="text-[10px] text-emerald-200 uppercase font-medium">Employees</div>
                </div>
            </div>

            <div class="relative z-10 text-xs text-emerald-200/80 flex items-center justify-between">
                <span>&copy; {{ date('Y') }} PT Kalbe Morinaga Indonesia</span>
                <span>v2.0</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:col-span-7 p-8 md:p-12 flex flex-col justify-center">
            <div class="max-w-md mx-auto w-full">
                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Selamat Datang Kembali</h2>
                    <p class="text-sm text-gray-500 mt-1">Masukkan kredensial akun KMI Anda untuk masuk ke sistem.</p>
                </div>

                @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="txtEmail" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="txtEmail" name="txtEmail" value="{{ old('txtEmail', 'nrs@kalbe.co.id') }}" required
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                                placeholder="name@kalbe.co.id">
                        </div>
                    </div>

                    <div>
                        <label for="txtPassword" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="txtPassword" name="txtPassword" value="123456" required
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#006838] focus:ring-[#006838]">
                            <span>Ingat saya</span>
                        </label>
                        <a href="{{ route('register') }}" class="font-semibold text-[#006838] hover:underline">Daftar Akun Baru</a>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 mt-4 cursor-pointer">
                        <span>Masuk ke Sistem</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- Quick Login Demo Switcher -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 font-semibold mb-2">Akun Demo Cepat (Password: 123456):</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="setLogin('nrs@kalbe.co.id', '123456')" class="p-2 rounded-lg bg-gray-50 hover:bg-emerald-50 border border-gray-200 text-left transition">
                            <span class="block font-bold text-[#006838]">Dept Head MDP</span>
                            <span class="text-[10px] text-gray-500">nrs@kalbe.co.id (NRS)</span>
                        </button>
                        <button type="button" onclick="setLogin('ami@kalbe.co.id', '123456')" class="p-2 rounded-lg bg-gray-50 hover:bg-emerald-50 border border-gray-200 text-left transition">
                            <span class="block font-bold text-[#006838]">SPV MD/IT & AM</span>
                            <span class="text-[10px] text-gray-500">ami@kalbe.co.id (AMI)</span>
                        </button>
                        <button type="button" onclick="setLogin('aho@kalbe.co.id', '123456')" class="p-2 rounded-lg bg-gray-50 hover:bg-emerald-50 border border-gray-200 text-left transition">
                            <span class="block font-bold text-[#006838]">Employee (AHO)</span>
                            <span class="text-[10px] text-gray-500">aho@kalbe.co.id (AHO)</span>
                        </button>
                        <button type="button" onclick="setLogin('superadmin@kalbe.co.id', '123456')" class="p-2 rounded-lg bg-gray-50 hover:bg-emerald-50 border border-gray-200 text-left transition">
                            <span class="block font-bold text-[#006838]">Superadmin</span>
                            <span class="text-[10px] text-gray-500">superadmin@kalbe.co.id</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setLogin(email, password) {
            document.getElementById('txtEmail').value = email;
            document.getElementById('txtPassword').value = password;
        }
    </script>
</body>

</html>