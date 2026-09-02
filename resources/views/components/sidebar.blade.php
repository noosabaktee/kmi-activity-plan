@php
$authUser = \App\Models\MUser::with(['department', 'subDepartment'])->find(session('auth_user_id'));
$can = fn (string $ability) => $authUser && \App\Support\RoleAccess::can($authUser, $ability);

$isReportActive = request()->routeIs('reports.*');
$isMasterActive = request()->routeIs('master.*');
@endphp

<!-- Backdrop Overlay for Hamburger Drawer -->
<div class="sidebar-overlay fixed inset-0 bg-black/50 backdrop-blur-xs z-40 hidden transition-opacity duration-300 cursor-pointer" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Slide-out Drawer Sidebar -->
<aside class="sidebar fixed inset-y-0 left-0 w-72 bg-[#004d29] text-white flex flex-col shrink-0 h-full max-h-screen z-50 transition-transform duration-300 ease-in-out -translate-x-full shadow-2xl" id="sidebar" aria-label="Sidebar Navigation">
    <!-- Brand Logo Area -->
    <div class="p-5 flex items-center justify-between border-b border-white/10 bg-[#003d20]">
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 no-underline text-white">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-md p-1 shrink-0">
                <img src="{{ asset('images/KDC.png') }}" alt="Kalbe" class="w-full h-full object-contain" onerror="this.outerHTML='<i class=\'fa-solid fa-leaf text-[#006838] text-xl\'></i>'">
            </div>
            <div>
                <h1 class="text-base font-extrabold tracking-tight leading-tight text-white m-0">KMI PLAN</h1>
                <p class="text-[10px] text-emerald-300 font-medium tracking-wider uppercase m-0">Activity & KPI System</p>
            </div>
        </a>
        <button type="button" class="text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeSidebar()" title="Tutup Menu">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto custom-scrollbar">
        <!-- 1. Dashboard -->
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard.index') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
            <span>Dashboard</span>
        </a>

        <!-- 2. Project -->
        @if ($can('projects'))
        <a href="{{ route('projects.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('projects.*') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <i class="fa-solid fa-diagram-project w-5 text-center text-base"></i>
            <span>Project</span>
        </a>
        @endif

        <!-- 3. Exposure S-Curve -->
        @if ($can('exposure'))
        <a href="{{ route('exposure.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('exposure.index') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <i class="fa-solid fa-chart-line w-5 text-center text-base"></i>
            <span>Exposure S-Curve</span>
        </a>
        @endif

        <!-- 4. Report Dropdown -->
        @if ($can('reports'))
        <div class="space-y-1" x-data="{ open: {{ $isReportActive ? 'true' : 'false' }} }">
            <button type="button" onclick="toggleReportMenu()" class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ $isReportActive ? 'bg-white/15 text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-waveform w-5 text-center text-base"></i>
                    <span>Report</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" id="reportCaret"></i>
            </button>
            <div class="pl-8 pr-1 py-1 space-y-1 {{ $isReportActive ? '' : 'hidden' }}" id="reportSubmenu">
                <a href="{{ route('reports.daily-tasks') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('reports.daily-tasks*') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-table-cells w-4 text-center"></i>
                    <span>Daily Task (Sheet)</span>
                </a>
                <a href="{{ route('reports.daily-plans') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('reports.daily-plans*') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-calendar-week w-4 text-center"></i>
                    <span>Daily Plan (Weekly)</span>
                </a>
                <a href="{{ route('reports.monthly-report') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('reports.monthly-report*') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-table-list w-4 text-center"></i>
                    <span>Monthly Report</span>
                </a>
            </div>
        </div>
        @endif

        <div class="pt-3 pb-1">
            <div class="h-px bg-white/10 mx-2"></div>
        </div>

        <!-- 5. Master Data (Admin / Superadmin / Head) -->
        @if ($can('master-data'))
        <a href="{{ route('master.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ $isMasterActive ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <i class="fa-solid fa-database w-5 text-center text-base"></i>
            <span>Master Data</span>
        </a>
        @endif

        <!-- 6. WA Scheduler (Superadmin) -->
        @if ($can('wa-scheduler'))
        <a href="{{ route('wa-scheduler.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('wa-scheduler.*') ? 'bg-[#8CC63F] text-[#004d29] font-bold shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <i class="fa-brands fa-whatsapp w-5 text-center text-base text-emerald-400"></i>
            <span>WA Scheduler</span>
        </a>
        @endif
    </nav>

    <!-- User Profile & Department Badge at Bottom -->
    <div class="p-3 bg-[#003d20] border-t border-white/10">
        <div class="flex items-center gap-3 p-2 rounded-xl bg-white/5">
            <div class="w-9 h-9 rounded-lg bg-[#8CC63F] text-[#004d29] font-bold flex items-center justify-center text-sm shadow">
                {{ strtoupper(substr($authUser->txtEmployeeName ?? 'U', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate m-0 leading-tight">{{ $authUser->txtEmployeeName ?? 'User' }}</p>
                <p class="text-[10px] text-emerald-300 font-medium truncate m-0">{{ $authUser->subDepartment?->txtSubDepartmentCode ?? $authUser->txtRole }} &bull; {{ $authUser->department?->txtDepartmentCode ?? 'MDP' }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-white/60 hover:text-red-400 p-1.5 rounded-lg transition" title="Logout">
                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            openSidebar();
        } else {
            closeSidebar();
        }
    }

    function openSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function toggleReportMenu() {
        const submenu = document.getElementById('reportSubmenu');
        const caret = document.getElementById('reportCaret');
        if (submenu) submenu.classList.toggle('hidden');
        if (caret) caret.classList.toggle('rotate-180');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
</script>