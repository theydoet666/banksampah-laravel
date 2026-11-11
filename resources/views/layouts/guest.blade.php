<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
            <div class="text-center space-y-2">
                <a href="/" class="inline-block">
                    <x-application-logo class="w-16 h-16 mx-auto" />
                </a>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</h2>
                    <p class="text-xs text-slate-400">{{ appSetting('tagline', 'Sistem Informasi Manajemen Terpadu') }}</p>
                </div>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-5 bg-white shadow-xs border border-slate-100 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
