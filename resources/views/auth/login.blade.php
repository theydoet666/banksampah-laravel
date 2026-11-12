<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email / No. Rekening / No. HP -->
        <div>
            <x-input-label for="email" :value="__('Email / No. Rekening / No. HP')" />
            <x-text-input id="email" class="block mt-1 w-full text-sm" type="text" name="email" :value="old('email')" required autofocus placeholder="admin@banksampah.test, BS-2026-00001, atau 0812..." />
            <p class="text-[11px] text-slate-400 mt-1">Nasabah dapat login menggunakan No. Rekening atau No. HP.</p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-5">
            <span class="text-xs text-slate-500">Warga belum punya rekening?</span>
            <a href="{{ route('nasabah.register') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                Daftar Nasabah Baru &rarr;
            </a>
        </div>

        <div class="flex items-center justify-end mt-4 pt-4 border-t border-slate-100">
            @if (Route::has('password.request'))
                <a class="underline text-xs text-slate-500 hover:text-slate-800 rounded-md focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-emerald-600 hover:bg-emerald-700">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
