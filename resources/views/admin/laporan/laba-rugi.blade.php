@extends('layouts.app')

@section('title', 'Laporan Laba/Rugi Operasional Bank Sampah Desa')
@section('breadcrumb', 'Laporan > Laba/Rugi Operasional Desa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Laporan Laba/Rugi Operasional Bank Sampah</h2>
            <p class="text-xs text-slate-500">Perhitungan marjin usaha desa antara penerimaan penjualan ke pengepul dan beban pembayaran tabungan warga</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.laporan.laba-rugi.pdf', ['tanggal_dari' => $tanggalDari, 'tanggal_sampai' => $tanggalSampai]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Ekspor PDF Laba/Rugi
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Laporan Transaksi
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('admin.laporan.laba-rugi') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Dari Tanggal:</label>
                <input type="date" name="tanggal_dari" value="{{ $tanggalDari }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Sampai Tanggal:</label>
                <input type="date" name="tanggal_sampai" value="{{ $tanggalSampai }}" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors">
                    Terapkan Periode
                </button>
            </div>
        </form>
    </div>

    <!-- Laba Rugi Highlights -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Pendapatan Jual Pengepul -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">(+) Pendapatan Penjualan</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ formatRupiah($totalPendapatanJual) }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Total {{ number_format($totalBeratJual, 1, ',', '.') }} kg ke pengepul</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
        </div>

        <!-- Beban Beli Setoran Warga -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider">(-) Beban Beli dari Warga</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ formatRupiah($totalBebanBeli) }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Total {{ number_format($totalBeratBeli, 1, ',', '.') }} kg dari seluruh unit</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
        </div>

        <!-- Laba Kotor Bersih -->
        <div class="{{ $labaKotor >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">(=) Laba Operasional Desa</span>
                    <h3 class="text-2xl font-bold mt-1">{{ formatRupiah($labaKotor) }}</h3>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Margin Keuntungan: {{ $marginPersen }}%</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Rincian Penjualan dalam Periode -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Transaksi Penjualan ke Pengepul Periode Ini</h4>
            <span class="text-xs text-slate-400">Total {{ $penjualanList->count() }} transaksi pengiriman</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Kode Transaksi</th>
                        <th class="py-3 px-4">Pengepul</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-right">Berat (kg)</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($penjualanList as $jual)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 font-mono font-medium text-emerald-700">
                                <a href="{{ route('admin.penjualan.show', $jual) }}" class="hover:underline">
                                    {{ $jual->kode_penjualan }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $jual->pengepul->nama_perusahaan ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $jual->tanggal_penjualan->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono">
                                {{ number_format($jual->total_berat, 2, ',', '.') }} kg
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                {{ formatRupiah($jual->total_pendapatan) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($jual->status_pembayaran === 'lunas')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                        Lunas
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">
                                        Piutang
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.penjualan.show', $jual) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 italic">
                                Tidak ada transaksi penjualan ke pengepul pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

