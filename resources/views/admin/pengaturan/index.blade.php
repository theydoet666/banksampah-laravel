@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('breadcrumb', 'Pengaturan / Identitas Bank Sampah')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Pengaturan Sistem</h2>
        <p class="text-xs text-slate-500">Profil unit bank sampah untuk kop surat, struk, dan identitas aplikasi</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Unit Bank Sampah *</label>
                <input type="text" name="nama_bank_sampah" value="{{ old('nama_bank_sampah', appSetting('nama_bank_sampah', 'Bank Sampah BAA')) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Lengkap *</label>
                <textarea name="alamat" rows="3" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat', appSetting('alamat')) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor Telepon / WA *</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp', appSetting('no_telp')) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email Resmi *</label>
                    <input type="email" name="email" value="{{ old('email', appSetting('email')) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tahun Berdiri</label>
                <input type="text" name="tahun_berdiri" value="{{ old('tahun_berdiri', appSetting('tahun_berdiri')) }}" maxlength="4" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="2020">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm">Simpan Pengaturan</button>
            </div>
        </form>
    </x-card>
</div>
@endsection

