@extends('layouts.app')

@section('title', 'Laporan Transaksi')
@section('breadcrumb', 'Laporan / Transaksi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Laporan Rekap Transaksi</h2>
            <p class="text-xs text-slate-500">Filter data mutasi bank sampah dan unduh laporan resmi</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.laporan.excel', request()->all()) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('admin.laporan.pdf', request()->all()) }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Jenis Transaksi</label>
                <select name="jenis" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    <option value="setor" {{ request('jenis') === 'setor' ? 'selected' : '' }}>Setoran</option>
                    <option value="tarik" {{ request('jenis') === 'tarik' ? 'selected' : '' }}>Penarikan</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs">Filter Data</button>
            </div>
        </form>
    </x-card>

    <!-- Ringkasan Periode Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <x-stat-card title="Total Sampah Terkumpul" :value="number_format($totalBerat, 2, ',', '.') . ' kg'" color="blue" />
        <x-stat-card title="Total Nilai Setoran" :value="formatRupiah($totalSetor)" color="green" />
        <x-stat-card title="Total Nilai Penarikan" :value="formatRupiah($totalTarik)" color="red" />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Kode TRX</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Nasabah</th>
                        <th class="py-3.5 px-6">Jenis</th>
                        <th class="py-3.5 px-6">Berat</th>
                        <th class="py-3.5 px-6">Nilai</th>
                        <th class="py-3.5 px-6">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksiList as $trx)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-6 font-mono font-medium text-xs text-slate-800">{{ $trx->kode_transaksi }}</td>
                            <td class="py-3 px-6 text-xs text-slate-500">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                            <td class="py-3 px-6 font-semibold text-slate-800">{{ $trx->nasabah->nama ?? '-' }}</td>
                            <td class="py-3 px-6">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ ucfirst($trx->jenis) }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-xs text-slate-600">{{ $trx->total_berat ? number_format($trx->total_berat, 2) . ' kg' : '-' }}</td>
                            <td class="py-3 px-6 font-bold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ formatRupiah($trx->total_nilai) }}
                            </td>
                            <td class="py-3 px-6 text-xs {{ $trx->status === 'aktif' ? 'text-slate-600' : 'text-rose-400 line-through' }}">{{ ucfirst($trx->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 text-xs">Tidak ada data transaksi pada periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksiList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transaksiList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

