@extends('layouts.nasabah')

@section('title', 'Profil Akun Nasabah')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Pengaturan Akun & Profil</h2>
        <p class="text-xs text-slate-500">Perbarui kontak dan ubah kata sandi login</p>
    </div>

    <!-- Informasi Profil -->
    <x-card title="Data Diri Nasabah">
        <form method="POST" action="{{ route('nasabah.profil.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor Rekening</label>
                <input type="text" value="{{ $nasabah->no_rekening ?? '-' }}" disabled class="w-full rounded-lg bg-slate-100 border-slate-200 text-sm font-mono text-slate-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-lg bg-slate-100 border-slate-200 text-sm text-slate-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">No. WhatsApp / HP *</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $nasabah->no_hp ?? '') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('no_hp') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Tinggal *</label>
                <textarea name="alamat" rows="3" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat', $nasabah->alamat ?? '') }}</textarea>
                @error('alamat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>

    <!-- Ganti Password -->
    <x-card title="Ganti Kata Sandi">
        <form method="POST" action="{{ route('nasabah.profil.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kata Sandi Saat Ini *</label>
                <input type="password" name="current_password" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('current_password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kata Sandi Baru *</label>
                <input type="password" name="password" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Ulangi Kata Sandi Baru *</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold">Ganti Kata Sandi</button>
            </div>
        </form>
    </x-card>
</div>
@endsection

