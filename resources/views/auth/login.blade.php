<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
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
<body class="font-sans antialiased text-slate-800 h-full selection:bg-emerald-500 selection:text-white bg-slate-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- ================= SISI KIRI (50%): HERO VISUAL & BRANDING ================= -->
        <div class="relative hidden lg:flex lg:w-1/2 flex-col justify-between p-10 xl:p-14 overflow-hidden bg-gradient-to-br from-slate-950 via-emerald-950 to-teal-950 text-white">
            
            <!-- Ambient Decorative Lights & Subtle Mesh -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:24px_24px] opacity-10 pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10 flex items-center gap-3.5">
                @if(appSetting('logo'))
                    <img src="{{ asset('storage/' . appSetting('logo')) }}" 
                         alt="{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}" 
                         class="w-12 h-12 object-contain rounded-xl bg-white/10 backdrop-blur-md p-1.5 border border-white/15 shadow-lg shadow-black/20">
                @else
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-950/40 border border-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-white leading-tight">
                        {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
                    </h1>
                    <p class="text-xs font-medium text-emerald-300/90 tracking-wide">
                        {{ appSetting('tagline', 'Sistem Pengelolaan Sampah Terpadu') }}
                    </p>
                </div>
            </div>

            <!-- Main Hero Message -->
            <div class="relative z-10 my-auto py-6 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-400/20 text-emerald-300 text-xs font-medium backdrop-blur-md mb-5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Ekosistem Sirkular & Tabungan Desa
                </div>

                <h2 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-[1.25] mb-4">
                    Ubah Sampah Jadi Berkah, Bangun Desa Lebih Sejahtera.
                </h2>
                
                <p class="text-sm text-slate-300 leading-relaxed font-light mb-7">
                    Solusi digital terintegrasi untuk pencatatan setoran warga di unit dusun, tabungan nasabah transparan, serta tata kelola penjualan sampah gudang induk.
                </p>

                <!-- 3 Pillars Feature Cards -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm">
                        <div class="text-base mb-1.5">💳</div>
                        <h3 class="text-xs font-semibold text-white mb-0.5">Tabungan Digital</h3>
                        <p class="text-[10px] text-slate-400 leading-tight">Saldo real-time & penarikan aman.</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm">
                        <div class="text-base mb-1.5">🏘️</div>
                        <h3 class="text-xs font-semibold text-white mb-0.5">Jangkauan Unit</h3>
                        <p class="text-[10px] text-slate-400 leading-tight">Pos setor di setiap dusun/RW.</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm">
                        <div class="text-base mb-1.5">📊</div>
                        <h3 class="text-xs font-semibold text-white mb-0.5">Akuntabel</h3>
                        <p class="text-[10px] text-slate-400 leading-tight">Laporan stok & laba rugi tertata.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Footer -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-4 border-t border-white/10">
                <span class="flex items-center gap-1.5 text-slate-300">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Akses Aman Terenkripsi
                </span>
                <span>&copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</span>
            </div>
        </div>

        <!-- ================= SISI KANAN (50%): FORM LOGIN SIMETRIS & PROFESIONAL ================= -->
        <div class="flex-1 lg:w-1/2 flex flex-col justify-between p-6 sm:p-10 lg:p-12 xl:p-16 bg-white min-h-screen">
            
            <!-- Top Action Bar (Kanan Atas: Quick Action) -->
            <div class="flex items-center justify-between w-full max-w-md mx-auto">
                <!-- Brand Mobile Only -->
                <div class="flex lg:hidden items-center gap-2.5">
                    @if(appSetting('logo'))
                        <img src="{{ asset('storage/' . appSetting('logo')) }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-xs">
                            BS
                        </div>
                    @endif
                    <span class="font-bold text-xs text-slate-800 leading-tight">
                        {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
                    </span>
                </div>

                <div class="hidden lg:block"></div>

                <!-- Tautan Buka Rekening / Registrasi Mandiri -->
                <div class="text-right text-xs">
                    <span class="text-slate-500">Warga baru?</span>
                    <a href="{{ route('nasabah.register') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                        Daftar Nasabah &rarr;
                    </a>
                </div>
            </div>

            <!-- Center Form Container (Proporsional & Presisi) -->
            <div class="w-full max-w-md mx-auto my-auto py-6">
                
                <!-- Heading -->
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Masuk ke Akun
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">
                        Silakan masukkan Email, Username, No. Rekening, atau No. HP Anda.
                    </p>
                </div>

                <!-- Session Status / Alert -->
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
                    @csrf

                    <!-- Kolom Identitas Akun -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Akun Masuk
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
                                   placeholder="Username, No. Rekening, No. HP, atau Email"
                                   class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                        </div>
                    </div>

                    <!-- Kolom Kata Sandi dengan Show/Hide Toggle -->
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
                                   placeholder="Masukkan kata sandi"
                                   class="w-full pl-10 pr-11 py-2.5 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">

                            <!-- Tombol Fitur Melihat Password (Eye Toggle) -->
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none cursor-pointer"
                                    :title="showPassword ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi'"
                                    aria-label="Toggle password visibility"
                                    tabindex="-1">
                                <!-- Icon Mata Terbuka -->
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Icon Mata Dicoret -->
                                <svg x-show="showPassword" x-cloak class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me" 
                                   type="checkbox" 
                                   name="remember"
                                   class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20 focus:ring-offset-0 cursor-pointer">
                            <span class="ms-2 text-xs text-slate-600">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk Utama -->
                    <div class="pt-1">
                        <button type="submit" 
                                class="w-full py-2.5 sm:py-3 px-4 rounded-xl text-white font-semibold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] shadow-md shadow-emerald-700/20 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                            <span>Masuk ke Sistem</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Box Petunjuk Nasabah (Kompak & Elegan) -->
                <div class="mt-5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600 flex items-start gap-2.5">
                    <span class="text-sm shrink-0 mt-px">💡</span>
                    <p class="leading-relaxed">
                        <strong class="text-slate-800">Petunjuk Nasabah:</strong> Masuk menggunakan <b>No. Rekening</b> atau <b>No. HP</b>. Password default adalah <b>6 digit terakhir No. HP</b> Anda.
                    </p>
                </div>

            </div>

            <!-- Bottom Right Footer -->
            <div class="w-full max-w-md mx-auto pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Status: Sistem Terintegrasi</span>
                <span>Bank Sampah Desa &bull; {{ date('Y') }}</span>
            </div>

        </div>

    </div>
</body>
</html>
