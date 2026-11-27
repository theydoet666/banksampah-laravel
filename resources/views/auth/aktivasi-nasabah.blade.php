<x-guest-layout>
    <div class="mb-5 text-center">
        <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Aktivasi Akun Nasabah</h2>
        <p class="text-xs text-slate-500 mt-1">Verifikasi No. Rekening, NIK, dan No. HP Anda untuk membuat kata sandi pribadi pertama kali</p>
    </div>

    <form method="POST" action="{{ route('nasabah.activate.store') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <div>
            <label for="no_rekening" class="block text-xs font-semibold text-slate-700 mb-1">
                No. Rekening Bank Sampah <span class="text-rose-500">*</span>
            </label>
            <input id="no_rekening"
                   type="text"
                   name="no_rekening"
                   value="{{ old('no_rekening') }}"
                   required
                   autofocus
                   placeholder="Contoh: BS-2026-00001"
                   class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150 font-mono">
            <x-input-error :messages="$errors->get('no_rekening')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1">
                    NIK KTP (16 Digit) <span class="text-rose-500">*</span>
                </label>
                <input id="nik"
                       type="text"
                       name="nik"
                       required
                       maxlength="16"
                       inputmode="numeric"
                       pattern="[0-9]{16}"
                       placeholder="16 digit NIK terdaftar"
                       class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150 font-mono">
                <x-input-error :messages="$errors->get('nik')" class="mt-1" />
            </div>

            <div>
                <label for="no_hp" class="block text-xs font-semibold text-slate-700 mb-1">
                    No. HP Terdaftar <span class="text-rose-500">*</span>
                </label>
                <input id="no_hp"
                       type="text"
                       name="no_hp"
                       required
                       placeholder="081234567890"
                       class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                Buat Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <input id="password"
                       :type="showPass ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="new-password"
                       placeholder="Minimal 8 karakter (10 karakter di production)"
                       class="w-full px-3.5 py-2 pr-10 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-emerald-600" tabindex="-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <input id="password_confirmation"
                   :type="showPass ? 'text' : 'password'"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   placeholder="Ketik ulang kata sandi baru"
                   class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-white font-semibold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-700/20 transition-colors">
            Aktivasi Akun &amp; Masuk
        </button>

        <div class="pt-2 text-center">
            <a href="{{ route('login') }}" class="text-xs font-medium text-emerald-700 hover:underline">
                &larr; Kembali ke Halaman Login
            </a>
        </div>
    </form>
</x-guest-layout>
