<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-slate-800">Daftar Nasabah Baru</h2>
        <p class="text-xs text-slate-500 mt-1">Buka rekening buku tabungan digital Bank Sampah Anda secara mandiri</p>
    </div>

    <form method="POST" action="{{ route('nasabah.register') }}" class="space-y-4">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="nama" :value="__('Nama Lengkap (sesuai KTP) *')" />
            <x-text-input id="nama" class="block mt-1 w-full text-xs" type="text" name="nama" :value="old('nama')" required autofocus placeholder="Contoh: Siti Aminah" />
            <x-input-error :messages="$errors->get('nama')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- No HP -->
            <div>
                <x-input-label for="no_hp" :value="__('No. HP / WhatsApp *')" />
                <x-text-input id="no_hp" class="block mt-1 w-full text-xs" type="text" name="no_hp" :value="old('no_hp')" required placeholder="081234567890" />
                <p class="text-[10px] text-slate-400 mt-0.5">Bisa digunakan untuk login ke portal HP.</p>
                <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
            </div>

            <!-- NIK -->
            <div>
                <x-input-label for="nik" :value="__('NIK (Opsional)')" />
                <x-text-input id="nik" class="block mt-1 w-full text-xs" type="text" name="nik" :value="old('nik')" placeholder="16 digit NIK KTP" maxlength="16" />
                <x-input-error :messages="$errors->get('nik')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Jenis Kelamin -->
            <div>
                <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin *')" />
                <select id="jenis_kelamin" name="jenis_kelamin" required class="block mt-1 w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-1" />
            </div>

            <!-- Unit Dusun / RW -->
            <div>
                <x-input-label for="unit_id" :value="__('Unit Dusun/RW Terdekat *')" />
                <select id="unit_id" name="unit_id" required class="block mt-1 w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
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
            <x-input-label for="alamat" :value="__('Alamat Rumah Lengkap *')" />
            <textarea id="alamat" name="alamat" rows="2" required placeholder="Nama jalan, RT/RW, Dusun..." class="block mt-1 w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat') }}</textarea>
            <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
        </div>

        <!-- Password Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-100">
            <div>
                <x-input-label for="password" :value="__('Buat Kata Sandi *')" />
                <x-text-input id="password" class="block mt-1 w-full text-xs" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Ulangi Kata Sandi *')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full text-xs" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors text-center">
                Daftar & Terbitkan Buku Tabungan
            </button>
        </div>

        <div class="text-center pt-2">
            <span class="text-xs text-slate-500">Sudah memiliki buku tabungan?</span>
            <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                Masuk di Sini
            </a>
        </div>
    </form>
</x-guest-layout>
