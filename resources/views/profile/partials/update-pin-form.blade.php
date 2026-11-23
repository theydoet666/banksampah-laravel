<section>
    <header class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-800">
                    PIN Otorisasi Transaksi
                </h2>
                @if(auth()->user()->hasPin())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        PIN Aktif
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Belum Diatur
                    </span>
                @endif
            </div>

            <p class="mt-1 text-xs text-slate-500">
                PIN 6-digit angka digunakan untuk mengotorisasi dan mengonfirmasi pencairan saldo tabungan nasabah kas desa.
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('profile.pin.update') }}" class="mt-5 space-y-4 max-w-xl">
        @csrf
        @method('put')

        @if(auth()->user()->hasPin())
            <div>
                <label for="pin_current_password" class="block text-xs font-semibold text-slate-700 mb-1">
                    Password Akun Saat Ini <span class="text-rose-500">*</span>
                </label>
                <input id="pin_current_password" 
                       name="current_password" 
                       type="password" 
                       required 
                       placeholder="Masukkan password akun untuk verifikasi" 
                       class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs" 
                       autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="pin" class="block text-xs font-semibold text-slate-700 mb-1">
                    PIN Otorisasi Baru (6 Digit) <span class="text-rose-500">*</span>
                </label>
                <input id="pin" 
                       name="pin" 
                       type="password" 
                       inputmode="numeric" 
                       pattern="[0-9]{6}" 
                       maxlength="6" 
                       minlength="6" 
                       required 
                       placeholder="••••••" 
                       class="w-full text-center tracking-[0.4em] font-mono font-bold text-sm rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs" 
                       autocomplete="new-password" />
                <x-input-error :messages="$errors->get('pin')" class="mt-1" />
            </div>

            <div>
                <label for="pin_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                    Ulangi PIN Baru <span class="text-rose-500">*</span>
                </label>
                <input id="pin_confirmation" 
                       name="pin_confirmation" 
                       type="password" 
                       inputmode="numeric" 
                       pattern="[0-9]{6}" 
                       maxlength="6" 
                       minlength="6" 
                       required 
                       placeholder="••••••" 
                       class="w-full text-center tracking-[0.4em] font-mono font-bold text-sm rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs" 
                       autocomplete="new-password" />
                <x-input-error :messages="$errors->get('pin_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition-colors">
                {{ auth()->user()->hasPin() ? 'Perbarui PIN Otorisasi' : 'Buat PIN Otorisasi Baru' }}
            </button>

            @if (session('status') === 'pin-updated')
                <p x-data="{ show: true }" 
                   x-show="show" 
                   x-transition 
                   x-init="setTimeout(() => show = false, 3000)" 
                   class="text-xs text-emerald-600 font-semibold">
                    Tersimpan.
                </p>
            @endif
        </div>
    </form>
</section>
