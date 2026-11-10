@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('breadcrumb', 'Transaksi / Riwayat')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Transaksi</h2>
            <p class="text-xs text-slate-500">Pencatatan mutasi transaksi setoran dan penarikan saldo</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.transaksi.setor.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Setor Sampah
            </a>
            <a href="{{ route('admin.transaksi.tarik.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-xs inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Tarik Saldo
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('admin.transaksi.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-500 uppercase mb-1">Cari Kode / Nasabah</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kode atau nama..." class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block font-semibold text-slate-500 uppercase mb-1">Jenis Transaksi</label>
                <select name="jenis" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    <option value="setor" {{ request('jenis') === 'setor' ? 'selected' : '' }}>Setor Sampah</option>
                    <option value="tarik" {{ request('jenis') === 'tarik' ? 'selected' : '' }}>Tarik Saldo</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-500 uppercase mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void (Batal)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs">Terapkan Filter</button>
                @if(request()->hasAny(['search', 'jenis', 'status', 'tanggal_dari', 'tanggal_sampai']))
                    <a href="{{ route('admin.transaksi.index') }}" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Kode Transaksi</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Nasabah</th>
                        <th class="py-3.5 px-6">Jenis</th>
                        <th class="py-3.5 px-6">Total Berat</th>
                        <th class="py-3.5 px-6">Total Nilai</th>
                        <th class="py-3.5 px-6">Petugas</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksiList as $trx)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-6 font-mono font-medium text-xs text-slate-800">{{ $trx->kode_transaksi }}</td>
                            <td class="py-3.5 px-6 text-xs text-slate-500">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-6">
                                <a href="{{ route('admin.nasabah.show', $trx->nasabah_id) }}" class="font-semibold text-slate-800 hover:text-emerald-600">
                                    {{ $trx->nasabah->nama ?? '-' }}
                                </a>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ ucfirst($trx->jenis) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-600">
                                {{ $trx->total_berat ? number_format($trx->total_berat, 2, ',', '.') . ' kg' : '-' }}
                            </td>
                            <td class="py-3.5 px-6 font-bold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-500">{{ $trx->petugas->name ?? '-' }}</td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $trx->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400 line-through' }}">
                                    {{ ucfirst($trx->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.transaksi.show', $trx) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.transaksi.struk', $trx) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50" title="Cetak Struk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400 text-xs">Belum ada riwayat transaksi</td>
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

