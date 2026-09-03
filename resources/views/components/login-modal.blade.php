<div class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[2000] flex items-center justify-center p-4 {{ $errors->any() ? '' : 'hidden' }}" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitle">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 md:p-8 shadow-2xl border border-gray-100 relative animate-scale-in" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center shadow-xs border border-gray-200 p-1">
                    <img src="{{ asset('images/KDC.png') }}" alt="Kalbe" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fa-solid fa-leaf text-[#006838]\'></i>'">
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 tracking-tight m-0" id="loginModalTitle">Masuk ke Sistem</h3>
                    <p class="text-[11px] text-gray-500 m-0">KMI Activity Plan &bull; MDP</p>
                </div>
            </div>
            <button type="button" onclick="closeLoginModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 flex items-center justify-center transition cursor-pointer" aria-label="Close modal">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="loginModalEmail" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-400 text-sm pointer-events-none z-10 w-5">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" id="loginModalEmail" name="txtEmail" value="{{ old('txtEmail', 'head.mdp@kalbe.co.id') }}" required
                        style="padding-left: 2.75rem !important;"
                        class="w-full pr-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="name@kalbe.co.id">
                </div>
            </div>

            <div>
                <label for="loginModalPassword" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-400 text-sm pointer-events-none z-10 w-5">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="loginModalPassword" name="txtPassword" value="123456" required
                        style="padding-left: 2.75rem !important;"
                        class="w-full pr-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:border-[#006838] focus:ring-2 focus:ring-[#006838]/20 outline-none transition"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-gray-600 select-none">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#006838] focus:ring-[#006838]">
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('register') }}" class="font-semibold text-[#006838] hover:underline text-xs">Daftar Akun</a>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 mt-2 cursor-pointer">
                <span>Masuk ke Sistem</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <!-- Quick Login Demo Buttons -->
        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-2">Akun Demo Cepat (Password: 123456):</p>
            <div class="grid grid-cols-2 gap-1.5 text-xs">
                <button type="button" onclick="setQuickLogin('head.mdp@kalbe.co.id', '123456')" class="p-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-gray-200 text-left transition cursor-pointer">
                    <span class="block font-bold text-[#006838] text-[11px]">Head MDP</span>
                    <span class="text-[9px] text-gray-400 truncate block">head.mdp@kalbe.co.id</span>
                </button>
                <button type="button" onclick="setQuickLogin('spv.it@kalbe.co.id', '123456')" class="p-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-gray-200 text-left transition cursor-pointer">
                    <span class="block font-bold text-[#006838] text-[11px]">Supervisor IT</span>
                    <span class="text-[9px] text-gray-400 truncate block">spv.it@kalbe.co.id</span>
                </button>
                <button type="button" onclick="setQuickLogin('nrs@kalbe.co.id', '123456')" class="p-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-gray-200 text-left transition cursor-pointer">
                    <span class="block font-bold text-[#006838] text-[11px]">Employee (NRS)</span>
                    <span class="text-[9px] text-gray-400 truncate block">nrs@kalbe.co.id</span>
                </button>
                <button type="button" onclick="setQuickLogin('superadmin@kalbe.co.id', '123456')" class="p-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-gray-200 text-left transition cursor-pointer">
                    <span class="block font-bold text-[#006838] text-[11px]">Superadmin</span>
                    <span class="text-[9px] text-gray-400 truncate block">superadmin@kalbe.co.id</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.openLoginModal = function() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setTimeout(() => {
                const emailInput = document.getElementById('loginModalEmail');
                if (emailInput) emailInput.focus();
            }, 100);
        }
    };

    window.closeLoginModal = function() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    window.setQuickLogin = function(email, password) {
        const emailInput = document.getElementById('loginModalEmail');
        const passwordInput = document.getElementById('loginModalPassword');
        if (emailInput) emailInput.value = email;
        if (passwordInput) passwordInput.value = password;
    };

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeLoginModal();
                }
            });
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeLoginModal();
        }
    });
</script>