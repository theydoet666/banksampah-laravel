@extends('layouts.app')

@section('title', 'Detail Penjualan - ' . $penjualan->kode_penjualan)
@section('breadcrumb', 'Penjualan > ' . $penjualan->kode_penjualan)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ openModal: {{ request()->has('pelunasan') ? 'true' : 'false' }} }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-800">{{ $penjualan->kode_penjualan }}</h2>
                @if($penjualan->status_pembayaran === 'lunas')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Lunas
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Piutang / Tempo
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1">Dicatat pada {{ $penjualan->created_at->format('d F Y, H:i') }} WIB oleh {{ $penjualan->pengelola->name ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($penjualan->status_pembayaran === 'piutang')
                <button type="button" @click="openModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Catat Pelunasan
                </button>
            @endif
            <a href="{{ route('admin.penjualan.surat-jalan', $penjualan) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Surat Jalan / Nota
            </a>
            <a href="{{ route('admin.penjualan.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Data Pengepul Pembeli</h4>
            <div class="text-xs space-y-1.5">
                <p class="text-base font-bold text-slate-800">{{ $penjualan->pengepul->nama_perusahaan }}</p>
                <p class="text-slate-600"><span class="text-slate-400">Kontak:</span> {{ $penjualan->pengepul->penanggung_jawab ?? '-' }} ({{ $penjualan->pengepul->no_hp ?? '-' }})</p>
                <p class="text-slate-600"><span class="text-slate-400">Alamat:</span> {{ $penjualan->pengepul->alamat ?? '-' }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ringkasan Penjualan</h4>
            <div class="text-xs space-y-1.5">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Tanggal Pengiriman:</span>
                    <span class="font-semibold text-slate-800">{{ $penjualan->tanggal_penjualan->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Total Berat Terangkut:</span>
                    <span class="font-bold text-slate-800 font-mono">{{ number_format($penjualan->total_berat, 2, ',', '.') }} kg</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Total Nilai Penjualan:</span>
                    <span class="font-bold text-emerald-700 text-sm font-mono">{{ formatRupiah($penjualan->total_pendapatan) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400">Status Pembayaran:</span>
                    @if($penjualan->status_pembayaran === 'lunas')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Lunas
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Piutang / Tempo
                        </span>
                    @endif
                </div>

                @if($penjualan->tanggal_pelunasan)
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span class="text-slate-400">Tanggal Pelunasan:</span>
                        <span class="font-semibold text-slate-800">{{ $penjualan->tanggal_pelunasan->format('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 text-slate-600">
                        <span class="text-slate-400">Metode Pelunasan:</span>
                        <span class="font-semibold uppercase text-slate-800">{{ $penjualan->metode_pelunasan }}</span>
                    </div>
                    @if($penjualan->catatan_pelunasan)
                        <div class="py-1 text-slate-500">
                            <span class="block text-[10px] text-slate-400">Bukti / Catatan Bayar:</span>
                            <span class="italic text-slate-700">{{ $penjualan->catatan_pelunasan }}</span>
                        </div>
                    @endif
                    @if($penjualan->diterimaOleh)
                        <div class="py-1 text-[11px] text-slate-400">
                            Diterima & dicatat oleh: <strong class="text-slate-700">{{ $penjualan->diterimaOleh->name }}</strong>
                        </div>
                    @endif
                @elseif($penjualan->status_pembayaran === 'piutang')
                    <div class="pt-2">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-amber-800 block">Menunggu Pembayaran</span>
                                <span class="text-[11px] text-amber-700">Pengepul belum melunasi tagihan ini</span>
                            </div>
                            <button type="button" @click="openModal = true" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs transition-colors">
                                Lunasi Sekarang
                            </button>
                        </div>
                    </div>
                @endif

                @if($penjualan->catatan)
                    <div class="pt-2 text-[11px] text-slate-500 italic">
                        Catatan Pengiriman: {{ $penjualan->catatan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Pelunasan Piutang -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display: none;" x-cloak>
        <div @click.away="openModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl text-left space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Pelunasan Piutang Penjualan</h4>
                    <p class="text-xs text-slate-500">Konfirmasi pembayaran kas dari mitra pengepul</p>
                </div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Mitra Pengepul:</span>
                    <strong class="text-slate-800">{{ $penjualan->pengepul->nama_perusahaan }}</strong>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>No. Penjualan:</span>
                    <span class="font-mono text-slate-700">{{ $penjualan->kode_penjualan }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200/60 flex justify-between items-center">
                    <span class="font-semibold text-slate-600">Total Tagihan Piutang:</span>
                    <span class="font-black text-emerald-700 text-base">{{ formatRupiah($penjualan->total_pendapatan) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.penjualan.pelunasan', $penjualan) }}" class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Pembayaran Diterima <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" 
                           name="tanggal_pelunasan" 
                           value="{{ date('Y-m-d') }}" 
                           max="{{ date('Y-m-d') }}" 
                           required 
                           class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Metode Pembayaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="metode_pelunasan" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs">
                        <option value="transfer">Transfer Bank</option>
                        <option value="tunai">Tunai / Kas Langsung</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan / Referensi Bukti Bayar (Opsional)
                    </label>
                    <input type="text" 
                           name="catatan_pelunasan" 
                           placeholder="Contoh: Transfer ke Rekening BRI Bank Sampah - Ref: 98124" 
                           class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-xs" />
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white shadow-xs transition-colors">
                        Konfirmasi Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>
                @if($penjualan->catatan)
                    <div class="pt-2 text-[11px] text-slate-500 italic">
                        Catatan: {{ $penjualan->catatan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rincian Item Sampah Terjual</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Jenis Sampah</th>
                        <th class="py-3 px-4 text-right">Berat (kg)</th>
                        <th class="py-3 px-4 text-right">Harga Jual / kg</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($penjualan->details as $idx => $item)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $item->jenisSampah->kategori->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $item->jenisSampah->nama ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-medium text-slate-700">
                                {{ number_format($item->berat, 2, ',', '.') }} kg
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                {{ formatRupiah($item->harga_jual_satuan) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                {{ formatRupiah($item->subtotal) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 font-bold bg-slate-50/50">
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase text-[11px] text-slate-500">Total Keseluruhan:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-800">{{ number_format($penjualan->total_berat, 2, ',', '.') }} kg</td>
                        <td></td>
                        <td class="py-3.5 px-4 text-right font-mono text-emerald-700 text-sm">{{ formatRupiah($penjualan->total_pendapatan) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

