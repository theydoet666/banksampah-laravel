@extends('layouts.app')

@section('title', 'Persetujuan Penarikan Saldo Nasabah')
@section('breadcrumb', 'Transaksi > Persetujuan Penarikan')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Persetujuan Penarikan Saldo</h2>
            <p class="text-xs text-slate-500">Otorisasi pencairan dana tabungan warga oleh Pengelola/Bendahara Desa</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile.edit') }}#pin-otorisasi" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Kelola PIN Otorisasi
            </a>
        </div>
    </div>

    <!-- Ringkasan Status -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-amber-700">Antrean Menunggu</p>
                <h3 class="text-2xl font-black text-amber-900">{{ $totalMenunggu }} <span class="text-xs font-medium text-amber-700">Pengajuan</span></h3>
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-700">Total Nominal Menunggu</p>
                <h3 class="text-xl font-black text-emerald-900">Rp {{ number_format($nominalMenunggu, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-500/10 text-slate-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">Peran Otorisasi</p>
                <p class="text-xs font-bold text-slate-800">Admin Pengelola Desa</p>
                <span class="text-[10px] text-slate-500">Mencegah defisit kas di tingkat unit</span>
            </div>
        </div>
    </div>

    <!-- Filter Tab & Unit -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Tab Status -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('admin.persetujuan-tarik.index', ['status' => 'menunggu_persetujuan']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $status === 'menunggu_persetujuan' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Menunggu ({{ $totalMenunggu }})
            </a>
            <a href="{{ route('admin.persetujuan-tarik.index', ['status' => 'disetujui']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $status === 'disetujui' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui
            </a>
            <a href="{{ route('admin.persetujuan-tarik.index', ['status' => 'ditolak']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $status === 'ditolak' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak
            </a>
            <a href="{{ route('admin.persetujuan-tarik.index', ['status' => 'semua']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $status === 'semua' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Riwayat
            </a>
        </div>

        <!-- Filter Unit -->
        <form method="GET" action="{{ route('admin.persetujuan-tarik.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <select name="unit_id" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Semua Unit Dusun/RW</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Tabel Pengajuan -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-100 font-semibold">
                        <th class="py-3.5 px-4">Kode / Tgl</th>
                        <th class="py-3.5 px-4">Nasabah</th>
                        <th class="py-3.5 px-4">Unit Dusun/RW</th>
                        <th class="py-3.5 px-4">Nominal</th>
                        <th class="py-3.5 px-4">Metode / Catatan</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi Otorisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $trx)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-800 block">{{ $trx->kode_transaksi }}</span>
                                <span class="text-slate-400 text-[11px]">{{ $trx->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $trx->nasabah->nama }}</span>
                                <span class="text-slate-500 text-[11px] font-mono">{{ $trx->nasabah->no_rekening }}</span>
                                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Saldo: {{ $trx->nasabah->formatted_saldo }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $trx->nasabah->unit->nama ?? 'Unit Umum' }}
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">Oleh: {{ $trx->petugas->name ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-rose-600 text-sm">
                                Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $trx->metode_penarikan ?? 'tunai') }}
                                </span>
                                @if($trx->catatan)
                                    <p class="text-[11px] text-slate-500 mt-1 italic">"{{ $trx->catatan }}"</p>
                                @endif
                                @if($trx->alasan_penolakan)
                                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">Alasan tolak: {{ $trx->alasan_penolakan }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                {!! $trx->status_badge !!}
                                @if($trx->disetujuiOleh)
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $trx->disetujuiOleh->name }} ({{ $trx->tanggal_persetujuan?->format('d/m/y H:i') }})</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($trx->status === 'menunggu_persetujuan')
                                    <div class="flex items-center justify-center gap-1.5" x-data="{ openApprove: false, openReject: false, pin: '', showPin: false }">
                                        <!-- Tombol Buka Modal Setujui (PIN) -->
                                        <button type="button" @click="openApprove = true; pin = ''" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs inline-flex items-center gap-1 shadow-xs transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Setujui
                                        </button>

                                        <!-- Modal Form Otorisasi PIN Setujui -->
                                        <div x-show="openApprove" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display: none;" x-cloak>
                                            <div @click.away="openApprove = false" class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl text-left space-y-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-slate-800 text-sm">Otorisasi Pencairan Kas</h4>
                                                        <p class="text-xs text-slate-500">Konfirmasi penarikan tabungan nasabah</p>
                                                    </div>
                                                </div>

                                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1.5 text-xs">
                                                    <div class="flex justify-between text-slate-500">
                                                        <span>Nasabah:</span>
                                                        <strong class="text-slate-800">{{ $trx->nasabah->nama }}</strong>
                                                    </div>
                                                    <div class="flex justify-between text-slate-500">
                                                        <span>No. Rekening:</span>
                                                        <span class="font-mono text-slate-700">{{ $trx->nasabah->no_rekening }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-slate-500">
                                                        <span>Unit Dusun/RW:</span>
                                                        <span class="text-slate-700">{{ $trx->nasabah->unit->nama ?? 'Unit Umum' }}</span>
                                                    </div>
                                                    <div class="pt-1.5 border-t border-slate-200/60 flex justify-between items-center">
                                                        <span class="font-semibold text-slate-600">Nominal Dicairkan:</span>
                                                        <span class="font-black text-rose-600 text-sm">Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>

                                                @if(! auth()->user()->hasPin())
                                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs space-y-2">
                                                        <p class="font-semibold">Akun Anda belum memiliki PIN Otorisasi!</p>
                                                        <p class="text-[11px] text-amber-700">Untuk keamanan kas, Anda harus membuat 6-digit PIN terlebih dahulu.</p>
                                                        <a href="{{ route('profile.edit') }}" class="inline-block text-xs font-bold text-emerald-700 underline">
                                                            Buat PIN Sekarang di Profil &rarr;
                                                        </a>
                                                    </div>
                                                @else
                                                    <form method="POST" action="{{ route('admin.persetujuan-tarik.setujui', $trx) }}" class="space-y-3">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                                                Masukkan PIN Otorisasi (6 Digit) <span class="text-rose-500">*</span>
                                                            </label>
                                                            <div class="relative">
                                                                <input :type="showPin ? 'text' : 'password'" 
                                                                       name="pin" 
                                                                       x-model="pin" 
                                                                       maxlength="6" 
                                                                       minlength="6" 
                                                                       pattern="[0-9]{6}" 
                                                                       inputmode="numeric" 
                                                                       required 
                                                                       placeholder="••••••" 
                                                                       class="w-full text-center tracking-[0.5em] text-lg font-mono font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 pr-10 shadow-xs" 
                                                                       autocomplete="off" />
                                                                <button type="button" @click="showPin = !showPin" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                                                    <svg x-show="!showPin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                    <svg x-show="showPin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                                                </button>
                                                            </div>
                                                            <div class="flex items-center justify-between text-[10px] mt-1">
                                                                <span class="text-slate-400">PIN 6 digit otorisasi kas.</span>
                                                                <a href="{{ route('profile.edit') }}#pin-otorisasi" target="_blank" class="text-emerald-700 hover:text-emerald-800 font-semibold underline">
                                                                    Lupa / Ganti PIN?
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center justify-end gap-2 pt-2">
                                                            <button type="button" @click="openApprove = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                                                Batal
                                                            </button>
                                                            <button type="submit" :disabled="pin.length !== 6" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white disabled:opacity-50 transition-colors shadow-xs">
                                                                Konfirmasi & Cairkan Dana
                                                            </button>
                                                        </div>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Tombol Tolak Modal -->
                                        <button type="button" @click="openReject = true" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold text-xs inline-flex items-center gap-1 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Tolak
                                        </button>

                                        <!-- Modal Form Tolak -->
                                        <div x-show="openReject" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display: none;" x-cloak>
                                            <div @click.away="openReject = false" class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl text-left space-y-4">
                                                <h4 class="font-bold text-slate-800 text-sm">Tolak Pengajuan Penarikan</h4>
                                                <p class="text-xs text-slate-500">Nominal: <strong class="text-slate-800">Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}</strong> ({{ $trx->nasabah->nama }})</p>
                                                
                                                <form method="POST" action="{{ route('admin.persetujuan-tarik.tolak', $trx) }}" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                                        <textarea name="alasan_penolakan" rows="3" required placeholder="Contoh: Kas fisik hari ini tidak mencukupi, harap ajukan kembali besok..." class="w-full text-xs rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500"></textarea>
                                                    </div>
                                                    <div class="flex items-center justify-end gap-2 pt-2">
                                                        <button type="button" @click="openReject = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-semibold text-white">Tolak Pengajuan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('admin.transaksi.show', $trx) }}" class="text-xs text-slate-600 font-semibold hover:text-emerald-600 underline">
                                        Lihat Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <p class="text-xs">Tidak ada data pengajuan penarikan pada status ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksis->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
