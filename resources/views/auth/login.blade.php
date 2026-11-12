<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk | {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 h-full selection:bg-emerald-500 selection:text-white">
    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- ================= SISI KIRI: BRANDING & HERO EXPERIENCE (DESKTOP) ================= -->
        <div class="relative hidden lg:flex lg:w-1/2 xl:w-7/12 flex-col justify-between p-12 xl:p-16 overflow-hidden bg-gradient-to-br from-slate-950 via-emerald-950 to-teal-950 text-white">
            
            <!-- Ambient Glow & Pattern Elements -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:28px_28px] opacity-10 pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="relative z-10 flex items-center gap-4">
                @if(appSetting('logo'))
                    <img src="{{ asset('storage/' . appSetting('logo')) }}" 
                         alt="{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}" 
                         class="w-13 h-13 object-contain rounded-2xl bg-white/10 backdrop-blur-md p-2 border border-white/15 shadow-xl shadow-black/20">
                @else
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-xl shadow-emerald-950/50 border border-white/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">
                        {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
                    </h1>
                    <p class="text-xs font-medium text-emerald-300/90 tracking-wide">
                        {{ appSetting('tagline', 'Sistem Pengelolaan Sampah Terpadu & Berkelanjutan') }}
                    </p>
                </div>
            </div>

            <!-- Hero Message & Value Proposition -->
            <div class="relative z-10 my-auto py-8 max-w-xl">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-400/20 text-emerald-300 text-xs font-medium backdrop-blur-md mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Portal Layanan Digital Terpadu Desa
                </div>

                <h2 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-[1.25] mb-5">
                    Ubah Sampah Jadi Berkah, Bangun Desa Lebih Sejahtera.
                </h2>
                
                <p class="text-sm xl:text-base text-slate-300 leading-relaxed font-light mb-8">
                    Platform digital resmi untuk memonitor tabungan sampah nasabah, transaksi unit dusun, hingga penjualan pengepul secara transparan dan akuntabel.
                </p>

                <!-- Value Pillar Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition duration-200">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-sm font-bold mb-2.5">
                            💳
                        </div>
                        <h3 class="text-xs font-semibold text-white mb-1">Tabungan Digital</h3>
                        <p class="text-[11px] text-slate-400 leading-snug">Saldo nasabah tercatat riil, aman, dan dapat ditarik sewaktu-waktu.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition duration-200">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center text-sm font-bold mb-2.5">
                            🏘️
                        </div>
                        <h3 class="text-xs font-semibold text-white mb-1">Jangkauan Dusun</h3>
                        <p class="text-[11px] text-slate-400 leading-snug">Pos setor tersebar di unit dusun untuk kemudahan warga sekitar.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm hover:bg-white/[0.07] transition duration-200">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 flex items-center justify-center text-sm font-bold mb-2.5">
                            📊
                        </div>
                        <h3 class="text-xs font-semibold text-white mb-1">Sirkular & Rapi</h3>
                        <p class="text-[11px] text-slate-400 leading-snug">Laporan stok gudang, tonase daur ulang, dan laba rugi terpadu.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Quote & Copyright -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-6 border-t border-white/10">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Aman & Terenkripsi
                </span>
                <span>&copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</span>
            </div>
        </div>

        <!-- ================= SISI KANAN: FORM LOGIN ELEGAN & INTERAKTIF ================= -->
        <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-10 lg:p-14 bg-gradient-to-b from-slate-50 to-slate-100 min-h-screen">
            
            <div class="w-full max-w-[440px]">

                <!-- Brand Khusus Mobile View -->
                <div class="flex lg:hidden items-center justify-center gap-3 mb-8">
                    @if(appSetting('logo'))
                        <img src="{{ asset('storage/' . appSetting('logo')) }}" 
                             alt="{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}" 
                             class="w-12 h-12 object-contain rounded-xl bg-white p-1.5 shadow-sm border border-slate-200">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-700/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    @endif
                    <div class="text-left">
                        <h2 class="text-base font-bold text-slate-800 leading-tight">
                            {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{ appSetting('tagline', 'Sistem Pengelolaan Sampah Terpadu') }}
                        </p>
                    </div>
                </div>

                <!-- Main Card Container -->
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-150 overflow-hidden relative transition-all duration-300">
                    
                    <!-- Decorative Top Accent Bar -->
                    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600"></div>

                    <div class="p-7 sm:p-9">
                        
                        <!-- Header Form -->
                        <div class="mb-7">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                                Selamat Datang
                            </h2>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                Masuk menggunakan Email, Nomor Rekening, atau Nomor HP Anda.
                            </p>
                        </div>

                        <!-- Session Status / Alert -->
                        @if (session('status'))
                            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-700 text-xs space-y-1">
                                <div class="font-semibold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Autentikasi Gagal
                                </div>
                                <p class="text-[11px] text-rose-600 pl-5">
                                    {{ $errors->first() }}
                                </p>
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
                            @csrf

                            <!-- Identifier: Email / No. Rekening / No. HP -->
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Email / No. Rekening / No. HP
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input id="email" 
                                           name="email" 
                                           type="text" 
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus 
                                           autocomplete="username"
                                           placeholder="BS-2026-00001, 0812..., atau email"
                                           class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/60 hover:bg-white focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150 @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-200 @enderror">
                                </div>
                                <span class="text-[11px] text-slate-400 mt-1 block">
                                    Warga dapat mengisi Nomor Rekening atau Nomor HP yang terdaftar.
                                </span>
                            </div>

                            <!-- Password with Show/Hide Toggle -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-xs font-semibold text-slate-700">
                                        Kata Sandi
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" 
                                           class="text-[11px] font-medium text-emerald-600 hover:text-emerald-700 hover:underline">
                                            Lupa kata sandi?
                                        </a>
                                    @endif
                                </div>

                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>

                                    <input id="password" 
                                           name="password" 
                                           :type="showPassword ? 'text' : 'password'" 
                                           required 
                                           autocomplete="current-password"
                                           placeholder="Masukkan kata sandi Anda"
                                           class="w-full pl-10 pr-11 py-2.5 text-xs sm:text-sm bg-slate-50/60 hover:bg-white focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150 @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-200 @enderror">

                                    <!-- Tombol Fitur Melihat Password (Eye Toggle) -->
                                    <button type="button" 
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none"
                                            :title="showPassword ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi'"
                                            aria-label="Toggle password visibility"
                                            tabindex="-1">
                                        <!-- Icon Mata Terbuka (saat tersembunyi) -->
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <!-- Icon Mata Dicoret (saat terlihat) -->
                                        <svg x-show="showPassword" x-cloak class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me Checkbox -->
                            <div class="flex items-center justify-between pt-1">
                                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                                    <input id="remember_me" 
                                           type="checkbox" 
                                           name="remember"
                                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 focus:ring-offset-0 transition duration-150 cursor-pointer">
                                    <span class="ms-2 text-xs text-slate-600 font-medium">Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <!-- Tombol Submit / Masuk -->
                            <button type="submit" 
                                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-xs sm:text-sm bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:via-teal-500 hover:to-emerald-600 active:scale-[0.99] shadow-lg shadow-emerald-700/20 hover:shadow-emerald-700/30 transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
                                <span>Masuk ke Akun</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </form>

                        <!-- Box Panduan Nasabah Warga (Sangat Informatif & Solutif) -->
                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <div class="rounded-2xl bg-gradient-to-br from-emerald-50/70 to-teal-50/40 border border-emerald-150/70 p-3.5 text-left">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-600/10 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[11px] font-bold text-emerald-900 leading-tight">
                                            Petunjuk Login Nasabah Warga
                                        </p>
                                        <p class="text-[10px] text-emerald-800/90 leading-relaxed">
                                            Jika didaftarkan oleh petugas loket/pos dusun, gunakan <b>No. Rekening</b> atau <b>No. HP</b> dengan kata sandi awal: <b>6 digit terakhir No. HP</b> Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Register Nasabah Baru -->
                        <div class="mt-4 text-center">
                            <p class="text-xs text-slate-500">
                                Belum memiliki buku tabungan sampah?
                            </p>
                            <a href="{{ route('nasabah.register') }}" 
                               class="inline-flex items-center gap-1.5 mt-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                <span>Buka Rekening Nasabah Baru (Gratis)</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Footer Mini Luar Card -->
                <div class="mt-6 text-center text-[11px] text-slate-400">
                    <p>Sistem Informasi Bank Sampah &bull; Terhubung Antar-Unit Dusun</p>
                </div>

            </div>
        </div>

    </div>
</body>
</html>
