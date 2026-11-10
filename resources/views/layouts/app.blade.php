<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/50 z-20 md:hidden"></div>

        <!-- Main Content Area -->
        <div class="flex-1 md:ml-64 flex flex-col min-h-screen">
            <!-- Topbar Navigation -->
            @include('layouts.partials.topbar')

            <!-- Page Body -->
            <main class="p-6 flex-1">
                @include('layouts.partials.flash-message')
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="px-6 py-4 border-t border-slate-100 bg-white text-xs text-slate-400 text-center">
                &copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}. Hak Cipta Dilindungi.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
