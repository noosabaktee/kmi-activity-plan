<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'KMI Activity Plan - Kalbe Nutritionals' }}</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js CDN with fallback -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"><\/script>');
        }
    </script>

    <!-- Moment.js & Pikaday (Handsontable Date Editor Dependencies) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday@1.8.2/pikaday.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday@1.8.2/css/pikaday.css">

    <!-- Handsontable JS & CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

    <!-- App CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F7F4] text-[#222222] font-['Inter',sans-serif] min-h-screen flex flex-col antialiased">
    @include('components.sidebar')

    <main class="main-wrapper flex-1 flex flex-col min-w-0 w-full transition-all duration-300">
        @include('components.topbar', [
        'pageTitle' => $pageTitle ?? 'KMI ACTIVITY PLAN',
        'pageSubtitle' => $pageSubtitle ?? '<span>Monitor</span> &bull; <span>Plan</span> &bull; <span>Excel</span>',
        ])

        <div class="content-area p-4 md:p-6 lg:p-8 flex-1 w-full">
            @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm animate-fade-in">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm animate-fade-in">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-2 mb-1 font-semibold text-sm">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Terjadi kesalahan input:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 ml-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('modals')
    @include('components.delete-modal')
    @stack('scripts')
</body>

</html>