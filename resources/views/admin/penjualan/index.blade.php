@extends('layouts.app')

@section('title', 'Riwayat Penjualan Sampah')
@section('breadcrumb', 'Penjualan > Riwayat Penjualan ke Pengepul')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Penjualan Sampah ke Pengepul</h2>
            <p class="text-xs text-slate-500">Pencatatan distribusi dan penjualan sampah grosir dari gudang desa ke mitra daur ulang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.penjualan.stok') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Cek Stok Gudang
            </a>
            <a href="{{ route('admin.penjualan.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Penjualan Baru
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('admin.penjualan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="pengepul_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Pengepul</option>
                    @foreach($pengepulList as $p)
                        <option value="{{ $p->id }}" {{ request('pengepul_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_perusahaan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" placeholder="Dari tanggal" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" placeholder="Sampai tanggal" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['pengepul_id', 'tanggal_dari', 'tanggal_sampai']))
                    <a href="{{ route('admin.penjualan.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Kode Transaksi</th>
                        <th class="py-3 px-4">Pengepul</th>
                        <th class="py-3 px-4">Tanggal Jual</th>
                        <th class="py-3 px-4 text-right">Total Berat</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($penjualanList as $jual)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">
                                <a href="{{ route('admin.penjualan.show', $jual) }}" class="hover:underline">
                                    {{ $jual->kode_penjualan }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $jual->pengepul->nama_perusahaan ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $jual->tanggal_penjualan->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-slate-700">
                                {{ number_format($jual->total_berat, 1, ',', '.') }} kg
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-700">
                                {{ formatRupiah($jual->total_pendapatan) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($jual->status_pembayaran === 'lunas')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Piutang / Tempo
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $jual->pengelola->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.penjualan.show', $jual) }}" class="p-1.5 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 inline-block" title="Detail Penjualan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.penjualan.surat-jalan', $jual) }}" target="_blank" class="p-1.5 text-emerald-600 hover:text-emerald-700 rounded-lg hover:bg-emerald-50 inline-block" title="Cetak Surat Jalan / Nota">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    message="Belum ada transaksi penjualan ke pengepul" 
                                    submessage="Pilih 'Catat Penjualan Baru' untuk mencatat pengiriman sampah dari gudang desa ke mitra pembeli." 
                                    actionText="Catat Penjualan Baru" 
                                    :actionUrl="route('admin.penjualan.create')" 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($penjualanList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $penjualanList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
