<x-guest-layout>
    <div class="mb-5 text-center">
        <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Daftar Nasabah Baru</h2>
        <p class="text-xs text-slate-500 mt-1">Buka rekening buku tabungan digital Bank Sampah Anda secara mandiri</p>
    </div>

    <form method="POST" action="{{ route('nasabah.register') }}" class="space-y-4" x-data="{ showPass: false }">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1">
                Nama Lengkap (sesuai KTP) <span class="text-rose-500">*</span>
            </label>
            <input id="nama" 
                   type="text" 
                   name="nama" 
                   value="{{ old('nama') }}" 
                   required 
                   autofocus 
                   placeholder="Contoh: Siti Aminah" 
                   class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
            <x-input-error :messages="$errors->get('nama')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- No HP -->
            <div>
                <label for="no_hp" class="block text-xs font-semibold text-slate-700 mb-1">
                    No. HP / WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input id="no_hp" 
                       type="text" 
                       name="no_hp" 
                       value="{{ old('no_hp') }}" 
                       required 
                       placeholder="081234567890" 
                       class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                <p class="text-[10px] text-slate-400 mt-0.5">Digunakan untuk login di ponsel.</p>
                <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
            </div>

            <!-- NIK -->
            <div>
                <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1">
                    NIK <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input id="nik" 
                       type="text" 
                       name="nik" 
                       value="{{ old('nik') }}" 
                       placeholder="16 digit NIK KTP" 
                       maxlength="16" 
                       class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                <x-input-error :messages="$errors->get('nik')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Jenis Kelamin -->
            <div>
                <label for="jenis_kelamin" class="block text-xs font-semibold text-slate-700 mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select id="jenis_kelamin" 
                        name="jenis_kelamin" 
                        required 
                        class="w-full px-3 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-1" />
            </div>

            <!-- Unit Dusun / RW -->
            <div>
                <label for="unit_id" class="block text-xs font-semibold text-slate-700 mb-1">
                    Unit Dusun Terdekat <span class="text-rose-500">*</span>
                </label>
                <select id="unit_id" 
                        name="unit_id" 
                        required 
                        class="w-full px-3 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                    <option value="">-- Pilih Unit Domisili --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama }} ({{ $unit->kode_unit }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('unit_id')" class="mt-1" />
            </div>
        </div>

        <!-- Alamat -->
        <div>
            <label for="alamat" class="block text-xs font-semibold text-slate-700 mb-1">
                Alamat Rumah Lengkap <span class="text-rose-500">*</span>
            </label>
            <textarea id="alamat" 
                      name="alamat" 
                      rows="2" 
                      required 
                      placeholder="Nama jalan, RT/RW, Dusun..." 
                      class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">{{ old('alamat') }}</textarea>
            <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
        </div>

        <!-- Password Fields with Toggle -->
        <div class="pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-700">Keamanan Akun</span>
                <button type="button" @click="showPass = !showPass" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 focus:outline-none">
                    <span x-text="showPass ? 'Sembunyikan Sandi' : 'Lihat Sandi'"></span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-[11px] font-medium text-slate-600 mb-1">
                        Buat Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input id="password" 
                           :type="showPass ? 'text' : 'password'" 
                           name="password" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Min. 8 karakter" 
                           class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-[11px] font-medium text-slate-600 mb-1">
                        Ulangi Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input id="password_confirmation" 
                           :type="showPass ? 'text' : 'password'" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Ulangi sandi" 
                           class="w-full px-3.5 py-2 text-xs bg-slate-50/60 focus:bg-white rounded-xl border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition duration-150">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold text-xs bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.99] shadow-lg shadow-emerald-700/20 transition-all duration-200 cursor-pointer">
                Daftar & Terbitkan Buku Tabungan &rarr;
            </button>
        </div>

        <div class="text-center pt-2">
            <span class="text-xs text-slate-500">Sudah memiliki buku tabungan?</span>
            <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                Masuk ke Akun
            </a>
        </div>
    </form>
</x-guest-layout>
