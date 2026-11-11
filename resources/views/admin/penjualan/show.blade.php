@extends('layouts.app')

@section('title', 'Detail Penjualan - ' . $penjualan->kode_penjualan)
@section('breadcrumb', 'Penjualan > ' . $penjualan->kode_penjualan)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
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
        <div class="flex gap-2">
            <a href="{{ route('admin.penjualan.surat-jalan', $penjualan) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
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
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Total Nilai Penjualan:</span>
                    <span class="font-bold text-emerald-700 text-sm font-mono">{{ formatRupiah($penjualan->total_pendapatan) }}</span>
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

