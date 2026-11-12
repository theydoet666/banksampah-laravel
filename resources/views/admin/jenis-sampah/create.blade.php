@extends('layouts.app')

@section('title', 'Tambah Jenis Sampah')
@section('breadcrumb', 'Master Data / Jenis Sampah / Tambah')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Tambah Jenis Sampah Baru</h2>
            <p class="text-xs text-slate-500">Tentukan harga beli untuk nasabah dan harga jual standar ke pengepul</p>
        </div>
        <a href="{{ route('admin.jenis-sampah.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.jenis-sampah.store') }}" 
              class="space-y-4" 
              x-data="{ 
                  submitting: false,
                  satuan: 'kg',
                  beli: {{ old('harga_beli', 0) }},
                  jual: {{ old('harga_jual', 0) }},
                  get margin() { return Math.max(0, Number(this.jual || 0) - Number(this.beli || 0)); },
                  get persen() { 
                      var b = Number(this.beli || 0);
                      var j = Number(this.jual || 0);
                      return b > 0 ? (((j - b) / b) * 100).toFixed(1) : 0; 
                  }
              }" 
              @submit="submitting = true">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kategori Sampah <span class="text-rose-500">*</span></label>
                <select name="kategori_id" required class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Pilih Kategori...</option>
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Jenis Sampah <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Botol PET Bening, Kardus Box">
                @error('nama') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Satuan Ukur <span class="text-rose-500">*</span></label>
                <select name="satuan" x-model="satuan" required class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="gram">Gram (g)</option>
                </select>
            </div>

            <!-- Struktur Dua Harga (Beli & Jual) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Penetapan Harga Komoditas</span>
                    <span class="text-[10px] text-slate-500">Per <span x-text="satuan"></span></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-emerald-800 mb-1">
                            Harga Beli Nasabah (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="50" 
                               min="0" 
                               name="harga_beli" 
                               x-model.number="beli" 
                               required 
                               class="w-full rounded-xl border-emerald-300 bg-white text-xs font-mono font-bold text-emerald-700 focus:border-emerald-500 focus:ring-emerald-500" 
                               placeholder="0">
                        <span class="text-[10px] text-slate-400 mt-1 block">Saldo yang didapat nasabah.</span>
                        @error('harga_beli') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-sky-800 mb-1">
                            Harga Jual Pengepul (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               step="50" 
                               min="0" 
                               name="harga_jual" 
                               x-model.number="jual" 
                               required 
                               class="w-full rounded-xl border-sky-300 bg-white text-xs font-mono font-bold text-sky-700 focus:border-sky-500 focus:ring-sky-500" 
                               placeholder="0">
                        <span class="text-[10px] text-slate-400 mt-1 block">Patokan jual ke pabrik/pengepul.</span>
                        @error('harga_jual') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Preview Margin Otomatis -->
                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <span class="text-slate-500 text-[11px]">Estimasi Margin Kas Desa:</span>
                    <div class="text-right font-mono font-bold">
                        <span :class="margin > 0 ? 'text-emerald-700' : 'text-slate-500'">
                            Rp <span x-text="new Intl.NumberFormat('id-ID').format(margin)"></span>
                        </span>
                        <span class="text-[10px] font-normal text-slate-400" x-show="margin > 0">
                            (+<span x-text="persen"></span>%)
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Deskripsi & Syarat Kualitas</label>
                <textarea name="deskripsi" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Kondisi kering, tanpa tutup, sudah dipilah">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-xs font-medium text-slate-700 cursor-pointer">Aktifkan untuk transaksi setor & penjualan</label>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.jenis-sampah.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" :disabled="submitting" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold inline-flex items-center gap-2 disabled:opacity-60 shadow-sm cursor-pointer">
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Jenis Sampah'"></span>
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
