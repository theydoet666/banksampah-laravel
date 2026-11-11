<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan | {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-sky-50 border border-sky-200 text-sky-600 flex items-center justify-center shadow-sm">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-bold text-sky-600 uppercase tracking-widest bg-sky-100/70 px-3 py-1 rounded-full">Error 404 Not Found</span>
            <h1 class="text-2xl font-extrabold text-slate-800">Halaman Tidak Ditemukan</h1>
            <p class="text-sm text-slate-500">
                {{ $message ?? 'Halaman atau data yang Anda tuju tidak tersedia, telah dipindahkan, atau link yang dimasukkan salah.' }}
            </p>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button onclick="window.history.back()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-colors">
                Kembali
            </button>
            <a href="{{ auth()->check() ? (auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'petugas' ? route('petugas.dashboard') : route('nasabah.dashboard'))) : route('login') }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition-colors">
                Menuju Dashboard
            </a>
        </div>

        <div class="pt-6 border-t border-slate-200/60 text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }} &bull; Sistem Manajemen Sampah
        </div>
    </div>
</body>
</html>

