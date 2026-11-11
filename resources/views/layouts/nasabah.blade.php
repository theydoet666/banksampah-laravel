<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Nasabah') - {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>
    <meta name="description" content="{{ appSetting('deskripsi', 'Portal Buku Tabungan Digital Nasabah Bank Sampah BAA.') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    <div class="min-h-screen flex flex-col justify-between">
        <!-- Top Navbar -->
        <header class="bg-emerald-700 text-white shadow-md">
            <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if(appSetting('logo'))
                        <img src="{{ asset('storage/' . appSetting('logo')) }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg bg-white/10 p-1">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-emerald-800 flex items-center justify-center text-white font-bold">
                            BS
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm font-bold tracking-tight">{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</h1>
                        <span class="text-xs text-emerald-200">{{ appSetting('tagline', 'Buku Tabungan Digital') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm font-medium">
                    <a href="{{ route('nasabah.dashboard') }}" class="hover:text-emerald-200 transition-colors {{ request()->routeIs('nasabah.dashboard') ? 'underline underline-offset-4' : '' }}">Dashboard</a>
                    <a href="{{ route('nasabah.riwayat.index') }}" class="hover:text-emerald-200 transition-colors {{ request()->routeIs('nasabah.riwayat.*') ? 'underline underline-offset-4' : '' }}">Riwayat</a>
                    <a href="{{ route('nasabah.tarik.index') }}" class="hover:text-emerald-200 transition-colors {{ request()->routeIs('nasabah.tarik.*') ? 'underline underline-offset-4 font-bold text-amber-200' : '' }}">Tarik Saldo</a>
                    <a href="{{ route('nasabah.profil.edit') }}" class="hover:text-emerald-200 transition-colors {{ request()->routeIs('nasabah.profil.*') ? 'underline underline-offset-4' : '' }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-900 text-xs font-semibold text-white transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl w-full mx-auto px-4 py-8 flex-1">
            @include('layouts.partials.flash-message')
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }} &bull; Pilah Sampah Menjadi Berkah
        </footer>
    </div>

    <!-- Global Confirmation Modal -->
    <x-confirm-modal />
</body>
</html>

