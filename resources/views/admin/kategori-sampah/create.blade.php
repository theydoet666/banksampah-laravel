@extends('layouts.app')

@section('title', 'Tambah Kategori Sampah')
@section('breadcrumb', 'Master Data / Kategori Sampah / Tambah')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800">Tambah Kategori</h2>
        <a href="{{ route('admin.kategori-sampah.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.kategori-sampah.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Kategori *</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Plastik, Kertas, dll">
                @error('nama') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.kategori-sampah.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </x-card>
</div>
@endsection

