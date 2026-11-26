<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Nasabah') - {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>
    <meta name="description" content="{{ appSetting('deskripsi', 'Portal Buku Tabungan Digital Nasabah Bank Sampah BAA.') }}">

    <x-favicon />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-screen flex flex-col justify-between">
        <!-- Top Navbar -->
        <header class="bg-emerald-700 text-white shadow-md sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
                <!-- Brand / Logo -->
                <a href="{{ route('nasabah.dashboard') }}" class="flex items-center gap-2.5 min-w-0 hover:opacity-90 transition-opacity">
                    @if(appSetting('logo'))
                        <img src="{{ asset('storage/' . appSetting('logo')) }}" alt="Logo" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-lg bg-white/10 p-1 shrink-0">
                    @else
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-800 flex items-center justify-center text-white font-bold text-xs sm:text-sm shrink-0">
                            BS
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-xs sm:text-sm font-bold tracking-tight truncate">{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</h1>
                        <span class="text-[10px] sm:text-xs text-emerald-200 block truncate">{{ appSetting('tagline', 'Buku Tabungan Digital') }}</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-5 text-sm font-medium">
                    <a href="{{ route('nasabah.dashboard') }}" class="hover:text-emerald-200 transition-colors py-1 {{ request()->routeIs('nasabah.dashboard') ? 'font-bold text-white border-b-2 border-white' : 'text-emerald-100' }}">Dashboard</a>
                    <a href="{{ route('nasabah.riwayat.index') }}" class="hover:text-emerald-200 transition-colors py-1 {{ request()->routeIs('nasabah.riwayat.*') ? 'font-bold text-white border-b-2 border-white' : 'text-emerald-100' }}">Riwayat</a>
                    <a href="{{ route('nasabah.tarik.index') }}" class="hover:text-amber-200 transition-colors py-1 {{ request()->routeIs('nasabah.tarik.*') ? 'font-bold text-amber-200 border-b-2 border-amber-300' : 'text-emerald-100' }}">Tarik Saldo</a>
                    <a href="{{ route('nasabah.profil.edit') }}" class="hover:text-emerald-200 transition-colors py-1 {{ request()->routeIs('nasabah.profil.*') ? 'font-bold text-white border-b-2 border-white' : 'text-emerald-100' }}">Profil</a>
                    
                    <div class="h-4 w-px bg-emerald-500/60"></div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-900 text-xs font-semibold text-white transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center gap-2 md:hidden shrink-0">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            type="button" 
                            class="p-2 rounded-xl bg-emerald-800/80 hover:bg-emerald-800 text-white focus:outline-hidden"
                            aria-label="Toggle navigation">
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div x-show="mobileMenuOpen" 
                 @click.away="mobileMenuOpen = false" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t border-emerald-600/60 bg-emerald-800/95 backdrop-blur-md px-4 py-3 space-y-1 shadow-lg"
                 style="display: none;">
                <div class="px-3 py-2 border-b border-emerald-700/60 text-xs text-emerald-200 flex items-center justify-between">
                    <span>Nasabah: <strong class="text-white">{{ auth()->user()->name }}</strong></span>
                    <span class="font-mono text-[10px] bg-emerald-900/60 px-2 py-0.5 rounded">{{ auth()->user()->nasabah->no_rekening ?? '-' }}</span>
                </div>
                <a href="{{ route('nasabah.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('nasabah.dashboard') ? 'bg-emerald-600 text-white' : 'text-emerald-100 hover:bg-emerald-700/60' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard Tabungan
                </a>
                <a href="{{ route('nasabah.riwayat.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('nasabah.riwayat.*') ? 'bg-emerald-600 text-white' : 'text-emerald-100 hover:bg-emerald-700/60' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Riwayat Mutasi
                </a>
                <a href="{{ route('nasabah.tarik.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('nasabah.tarik.*') ? 'bg-amber-500 text-white' : 'text-amber-200 hover:bg-emerald-700/60' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><text x="12" y="15" text-anchor="middle" font-size="8.5" font-weight="700" font-family="ui-sans-serif, system-ui, sans-serif" fill="currentColor" stroke="none">Rp</text></svg>
                    Tarik Saldo Mandiri
                </a>
                <a href="{{ route('nasabah.profil.edit') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('nasabah.profil.*') ? 'bg-emerald-600 text-white' : 'text-emerald-100 hover:bg-emerald-700/60' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profil & Ganti Password
                </a>
                <div class="pt-2 border-t border-emerald-700/60">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-rose-600/90 hover:bg-rose-600 text-xs font-bold text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl w-full mx-auto px-4 py-6 sm:py-8 flex-1 pb-24 md:pb-8">
            @include('layouts.partials.flash-message')
            @yield('content')
        </main>

        <!-- Mobile App Bottom Navigation Bar -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 px-2 py-1 shadow-lg">
            <div class="grid grid-cols-4 items-center text-center">
                <a href="{{ route('nasabah.dashboard') }}" class="py-2 flex flex-col items-center gap-1 {{ request()->routeIs('nasabah.dashboard') ? 'text-emerald-700 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px]">Beranda</span>
                </a>
                <a href="{{ route('nasabah.riwayat.index') }}" class="py-2 flex flex-col items-center gap-1 {{ request()->routeIs('nasabah.riwayat.*') ? 'text-emerald-700 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-[10px]">Riwayat</span>
                </a>
                <a href="{{ route('nasabah.tarik.index') }}" class="py-2 flex flex-col items-center gap-1 {{ request()->routeIs('nasabah.tarik.*') ? 'text-emerald-700 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <div class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><text x="12" y="15" text-anchor="middle" font-size="8.5" font-weight="700" font-family="ui-sans-serif, system-ui, sans-serif" fill="currentColor" stroke="none">Rp</text></svg>
                    </div>
                    <span class="text-[10px]">Tarik Saldo</span>
                </a>
                <a href="{{ route('nasabah.profil.edit') }}" class="py-2 flex flex-col items-center gap-1 {{ request()->routeIs('nasabah.profil.*') ? 'text-emerald-700 font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-[10px]">Profil</span>
                </a>
            </div>
        </nav>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400 mb-14 md:mb-0">
            &copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }} &bull; Pilah Sampah Menjadi Berkah
        </footer>
    </div>

    <!-- Global Confirmation Modal -->
    <x-confirm-modal />
</body>
</html>

