<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased min-h-screen bg-gradient-to-b from-slate-50 via-emerald-50/20 to-slate-100 flex flex-col justify-center items-center py-8 px-4 sm:px-6 relative overflow-x-hidden selection:bg-emerald-500 selection:text-white">
        
        <!-- Ambient Background Glow -->
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Header Brand -->
            <div class="text-center mb-6">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-3 group">
                    <x-application-logo class="w-12 h-12 rounded-xl object-contain shadow-md transition-transform duration-200 group-hover:scale-105" />
                    <div class="text-left">
                        <h1 class="text-base font-bold text-slate-800 tracking-tight leading-tight">
                            {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}
                        </h1>
                        <p class="text-[11px] text-emerald-600 font-medium">
                            {{ appSetting('tagline', 'Sistem Pengelolaan Sampah Terpadu') }}
                        </p>
                    </div>
                </a>
            </div>

            <!-- Card Box -->
            <div class="w-full bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-150 overflow-hidden relative">
                <!-- Top Ribbon -->
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600"></div>

                <div class="p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer Small -->
            <div class="mt-6 text-center text-[11px] text-slate-400">
                &copy; {{ date('Y') }} {{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }} &bull; Hak Cipta Dilindungi
            </div>
        </div>
    </body>
</html>
