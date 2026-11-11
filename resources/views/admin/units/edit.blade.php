@extends('layouts.app')

@section('title', 'Edit Unit Bank Sampah')
@section('breadcrumb', 'Master Data > Unit Bank Sampah > Edit')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Edit Unit Bank Sampah</h2>
            <p class="text-xs text-slate-500">Perbarui data unit {{ $unit->nama }}</p>
        </div>
        <a href="{{ route('admin.units.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <form method="POST" action="{{ route('admin.units.update', $unit) }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Unit <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_unit" value="{{ old('kode_unit', $unit->kode_unit) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono @error('kode_unit') border-rose-500 @enderror">
                    @error('kode_unit')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="aktif" {{ old('status', $unit->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $unit->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Unit <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $unit->nama) }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 @error('nama') border-rose-500 @enderror">
                @error('nama')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penanggung Jawab / Ketua</label>
                    <input type="text" name="nama_ketua" value="{{ old('nama_ketua', $unit->nama_ketua) }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. Kontak / HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $unit->no_hp) }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Pos / Posko Unit</label>
                <textarea name="alamat" rows="2" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat', $unit->alamat) }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.units.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" x-bind:disabled="submitting" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 rounded-xl shadow-xs transition-colors">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submitting ? 'Menyimpan...' : 'Perbarui Unit'">Perbarui Unit</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
