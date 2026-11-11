@extends('layouts.app')

@section('title', 'Monitoring Stok Gudang Desa')
@section('breadcrumb', 'Penjualan > Stok Gudang Desa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Stok Fisik Sampah Gudang Desa</h2>
            <p class="text-xs text-slate-500">Monitoring volume sampah yang telah ditimbang dari seluruh unit dan siap dijual ke pengepul</p>
        </div>
        <div>
            <a href="{{ route('admin.penjualan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Jual Sampah ke Pengepul
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Masuk (Semua Unit)</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalMasuk, 1, ',', '.') }} <span class="text-xs font-normal text-slate-400">kg</span></h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Akumulasi setoran warga</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Terjual ke Pengepul</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalTerjual, 1, ',', '.') }} <span class="text-xs font-normal text-slate-400">kg</span></h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Sudah diangkut pengepul</p>
        </div>

        <div class="bg-emerald-500 text-white p-5 rounded-2xl shadow-sm">
            <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Sisa Stok Siap Jual</span>
            <h3 class="text-2xl font-bold mt-1">{{ number_format($totalBeratSisa, 1, ',', '.') }} <span class="text-xs font-normal text-emerald-100">kg</span></h3>
            <p class="text-[11px] text-emerald-100 mt-0.5">Tersedia di gudang desa</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Estimasi Nilai Stok</span>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ formatRupiah($totalEstimasiNilai) }}</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Berdasarkan harga acuan</p>
        </div>
    </div>

    <!-- Table Stok per Jenis Sampah -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rincian Stok per Jenis Sampah</h4>
            <span class="text-xs text-slate-400">Total {{ count($stokGudang) }} jenis sampah</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Nama Jenis Sampah</th>
                        <th class="py-3 px-4 text-right">Harga Beli Warga</th>
                        <th class="py-3 px-4 text-right">Total Masuk</th>
                        <th class="py-3 px-4 text-right">Total Terjual</th>
                        <th class="py-3 px-4 text-right font-bold text-emerald-700">Sisa Stok Gudang</th>
                        <th class="py-3 px-4 text-right">Estimasi Aset</th>
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
                            <td class="py-3 px-4 text-right text-slate-500 font-mono">
                                {{ formatRupiah($item['harga_beli']) }} / {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600">
                                {{ number_format($item['total_masuk'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-400">
                                {{ number_format($item['total_terjual'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $item['sisa_stok'] > 0 ? 'text-emerald-700 text-sm' : 'text-slate-300' }}">
                                {{ number_format($item['sisa_stok'], 1, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                {{ formatRupiah($item['estimasi_nilai']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 italic">
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
