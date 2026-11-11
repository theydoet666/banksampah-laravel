@extends('layouts.app')

@section('title', 'Tambah Mitra Pengepul')
@section('breadcrumb', 'Master Data > Mitra Pengepul > Tambah')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tambah Mitra Pengepul</h2>
            <p class="text-xs text-slate-500">Daftarkan pembeli grosir atau pabrik daur ulang</p>
        </div>
        <a href="{{ route('admin.pengepul.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <form method="POST" action="{{ route('admin.pengepul.store') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Perusahaan / Lapak Pengepul <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" required placeholder="Contoh: CV Daur Ulang Sejahtera" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                @error('nama_perusahaan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kontak / Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" placeholder="Nama PIC" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Gudang Pengepul</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap pabrik / gudang pengepul..." class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kemitraan <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.pengepul.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" x-bind:disabled="submitting" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 rounded-xl shadow-xs transition-colors">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Mitra'">Simpan Mitra</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

