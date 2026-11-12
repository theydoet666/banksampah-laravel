@extends('layouts.app')

@section('title', 'Laporan Stok Sampah Gudang')
@section('breadcrumb', 'Penjualan > Stok Gudang')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Stok Sampah Gudang Induk</h2>
            <p class="text-xs text-slate-500">Akumulasi fisik sampah dari setoran seluruh unit dusun yang siap dijual ke pengepul</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.penjualan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Penjualan ke Pengepul
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Sampah Terkumpul</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalMasuk, 1, ',', '.') }} <span class="text-xs font-normal text-slate-400">kg</span></h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Dari setoran warga seluruh dusun</p>
        </div>

        <div class="bg-emerald-600 text-white p-5 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Sisa Stok Siap Jual</span>
            <h3 class="text-2xl font-bold mt-1">{{ number_format($totalBeratSisa, 1, ',', '.') }} <span class="text-xs font-normal text-emerald-100">kg</span></h3>
            <p class="text-[11px] text-emerald-100 mt-0.5">Tersedia di gudang desa</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Modal Setoran (Harga Beli)</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ formatRupiah($totalEstimasiNilai) }}</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Beban saldo nasabah yang tersimpan</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-white to-emerald-50/30 shadow-xs">
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Potensi Penjualan Jual</span>
            <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ formatRupiah($totalEstimasiJual) }}</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-0.5">
                Estimasi Laba: +{{ formatRupiah($totalPotensiLaba) }}
            </p>
        </div>
    </div>

    <!-- Table Stok per Jenis Sampah -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rincian Stok & Valuasi per Jenis Sampah</h4>
            <span class="text-xs text-slate-400">Total {{ count($stokGudang) }} jenis sampah</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Nama Jenis Sampah</th>
                        <th class="py-3 px-4 text-right">Harga Beli Warga</th>
                        <th class="py-3 px-4 text-right">Std Jual Pengepul</th>
                        <th class="py-3 px-4 text-right">Total Masuk</th>
                        <th class="py-3 px-4 text-right">Total Terjual</th>
                        <th class="py-3 px-4 text-right font-bold text-emerald-700">Sisa Stok</th>
                        <th class="py-3 px-4 text-right">Nilai Modal (Beli)</th>
                        <th class="py-3 px-4 text-right font-bold text-emerald-700">Potensi Nilai Jual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($stokGudang as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $item['kategori'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $item['nama'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-700 font-mono font-medium">
                                {{ formatRupiah($item['harga_beli']) }} / {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-sky-700 font-mono font-medium">
                                {{ formatRupiah($item['harga_jual']) }} / {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 font-mono">
                                {{ number_format($item['total_masuk'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-400 font-mono">
                                {{ number_format($item['total_terjual'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold font-mono {{ $item['sisa_stok'] > 0 ? 'text-emerald-700 text-sm' : 'text-slate-300' }}">
                                {{ number_format($item['sisa_stok'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700">
                                {{ formatRupiah($item['estimasi_nilai_beli']) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                {{ formatRupiah($item['estimasi_nilai_jual']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400 italic">
                                Belum ada data jenis sampah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
