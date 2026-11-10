@extends('layouts.app')

@section('title', 'Edit Jenis Sampah')
@section('breadcrumb', 'Master Data / Jenis Sampah / Edit')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800">Edit Jenis Sampah</h2>
        <a href="{{ route('admin.jenis-sampah.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.jenis-sampah.update', $jenisSampah) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kategori Sampah *</label>
                <select name="kategori_id" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" {{ old('kategori_id', $jenisSampah->kategori_id) == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Jenis Sampah *</label>
                <input type="text" name="nama" value="{{ old('nama', $jenisSampah->nama) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('nama') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Satuan Ukur *</label>
                    <select name="satuan" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="kg" {{ old('satuan', $jenisSampah->satuan) === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="gram" {{ old('satuan', $jenisSampah->satuan) === 'gram' ? 'selected' : '' }}>Gram</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Harga Beli / Satuan (Rp) *</label>
                    <input type="number" step="0.01" name="harga_per_satuan" value="{{ old('harga_per_satuan', $jenisSampah->harga_per_satuan) }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <span class="text-[11px] text-slate-400">Jika harga diubah, sistem otomatis mencatat riwayat fluktuasi harga.</span>
                    @error('harga_per_satuan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Deskripsi Tambahan</label>
                <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('deskripsi', $jenisSampah->deskripsi) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $jenisSampah->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-slate-700">Aktifkan untuk transaksi</label>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.jenis-sampah.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</div>
@endsection

