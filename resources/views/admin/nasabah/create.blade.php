@extends('layouts.app')

@section('title', 'Tambah Nasabah Baru')
@section('breadcrumb', 'Master Data / Nasabah / Tambah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Form Pendaftaran Nasabah</h2>
            <p class="text-xs text-slate-500">Nomor rekening akan dibuat secara otomatis oleh sistem</p>
        </div>
        <a href="{{ roleRoute('nasabah.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <x-card>
        <form method="POST" action="{{ roleRoute('nasabah.store') }}" enctype="multipart/form-data" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('nama') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('nik') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">No. Handphone / WhatsApp *</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('no_hp') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Status Keanggotaan *</label>
                    <select name="status" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Unit Bank Sampah (Dusun/RW) *</label>
                @if(auth()->user()->isPetugas() && auth()->user()->unit_id)
                    <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                        <span>{{ auth()->user()->unit->nama }} ({{ auth()->user()->unit->kode_unit }})</span>
                        <span class="text-xs font-normal text-emerald-600">Unit Anda</span>
                    </div>
                    <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                @else
                    <select name="unit_id" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Pilih Unit Domisili Nasabah...</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->nama }} ({{ $u->kode_unit }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Alamat Lengkap *</label>
                <textarea name="alamat" rows="3" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat') }}</textarea>
                @error('alamat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Foto Profil / KTP (Opsional)</label>
                <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('foto') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if(auth()->user()->isAdmin())
            <div class="p-4 rounded-xl border border-sky-100 bg-sky-50/50 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Saldo Awal Pembukuan Manual (Khusus Admin)</h4>
                        <p class="text-xs text-slate-500">Opsional. Isi jika nasabah memiliki sisa saldo dari sistem buku tabungan manual lama yang ingin dimigrasikan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nominal Saldo Awal (Rp)</label>
                        <input type="number" name="saldo_awal" value="{{ old('saldo_awal', 0) }}" min="0" step="100" class="w-full rounded-lg border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500 font-semibold text-slate-800" placeholder="0">
                        @error('saldo_awal') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tanggal Saldo Awal</label>
                        <input type="date" name="tanggal_saldo_awal" value="{{ old('tanggal_saldo_awal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500">
                        @error('tanggal_saldo_awal') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Catatan / Keterangan Migrasi</label>
                        <input type="text" name="catatan_saldo_awal" value="{{ old('catatan_saldo_awal', 'Saldo awal migrasi pembukuan manual') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="Contoh: Saldo buku tabungan manual No. 12">
                        @error('catatan_saldo_awal') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ roleRoute('nasabah.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold">Batal</a>
                <button type="submit" :disabled="submitting" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 disabled:opacity-60">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan Nasabah'"></span>
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection

