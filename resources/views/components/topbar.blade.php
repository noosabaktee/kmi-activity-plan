@php
$authUser = \App\Models\MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
@endphp

<header class="topbar sticky top-0 bg-white/90 backdrop-blur-md border-b border-[#DDE5DD] z-40 px-4 md:px-6 py-3.5 flex items-center justify-between shadow-xs">
    <div class="flex items-center gap-3">
        <button type="button" class="p-2.5 rounded-xl text-[#006838] bg-[#EBF5E9]/70 hover:bg-[#EBF5E9] hover:text-[#004d29] border border-emerald-200/70 transition cursor-pointer flex items-center justify-center shadow-2xs group" onclick="toggleSidebar()" aria-label="Toggle Menu" title="Buka Menu Navigasi">
            <i class="fa-solid fa-bars-staggered text-lg group-hover:scale-105 transition-transform"></i>
        </button>
        <div>
            <h2 class="text-base md:text-lg font-extrabold text-[#006838] tracking-tight leading-tight m-0">{{ $pageTitle ?? 'KMI ACTIVITY PLAN' }}</h2>
            <p class="text-xs text-gray-500 font-medium m-0 flex items-center gap-1.5">{!! $pageSubtitle ?? '<span>Monitor</span> &bull; <span>Plan</span> &bull; <span>Excel</span>' !!}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if ($authUser)
        <!-- Department Badge -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#EBF5E9] border border-emerald-200 text-[#006838] text-xs font-semibold shadow-2xs">
            <i class="fa-solid fa-building text-emerald-600"></i>
            <span>{{ $authUser->department?->txtDepartmentCode ?? 'MDP' }} ({{ $authUser->subDepartment?->txtSubDepartmentCode ?? $authUser->txtRole }})</span>
        </div>

        <!-- Date indicator -->
        <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-500 font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
            <i class="fa-regular fa-calendar text-[#8CC63F]"></i>
            <span>{{ now()->translatedFormat('d M Y') }}</span>
        </div>

        <!-- Profile Link -->
        <a href="{{ route('profile.show') }}" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition no-underline" title="My Profile">
            <div class="w-8 h-8 rounded-lg bg-[#006838] text-white font-bold flex items-center justify-center text-xs shadow-sm">
                {{ strtoupper(substr($authUser->txtEmployeeName ?? 'U', 0, 2)) }}
            </div>
            <div class="hidden xl:block text-left">
                <span class="block text-xs font-bold text-gray-800 leading-none">{{ $authUser->txtEmployeeName ?? 'User' }}</span>
                <span class="block text-[10px] text-gray-500 leading-tight mt-0.5">{{ $authUser->txtRole }}</span>
            </div>
        </a>
        @else
        <!-- Date indicator -->
        <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-500 font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
            <i class="fa-regular fa-calendar text-[#8CC63F]"></i>
            <span>{{ now()->translatedFormat('d M Y') }}</span>
        </div>

        <!-- Login Button for Guest -->
        <button type="button" onclick="openLoginModal()" class="px-4 py-2 rounded-xl bg-[#006838] hover:bg-[#004d29] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-right-to-bracket text-[#8CC63F]"></i>
            <span>Masuk / Login</span>
        </button>
        @endif
    </div>
</header>