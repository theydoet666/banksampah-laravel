@extends('layouts.nasabah')

@section('title', 'Riwayat Transaksi Saya')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Mutasi Tabungan</h2>
            <p class="text-xs text-slate-500">Seluruh pencatatan setoran dan penarikan saldo Anda</p>
        </div>
        <a href="{{ route('nasabah.dashboard') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">&larr; Kembali ke Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-3.5 px-6">Kode Transaksi</th>
                    <th class="py-3.5 px-6">Tanggal</th>
                    <th class="py-3.5 px-6">Jenis</th>
                    <th class="py-3.5 px-6">Berat</th>
                    <th class="py-3.5 px-6">Nilai (Rp)</th>
                    <th class="py-3.5 px-6 text-right">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transaksiList as $trx)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3.5 px-6 font-mono text-xs font-semibold text-slate-800">{{ $trx->kode_transaksi }}</td>
                        <td class="py-3.5 px-6 text-xs text-slate-500">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                        <td class="py-3.5 px-6">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ ucfirst($trx->jenis) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-xs text-slate-600">{{ $trx->total_berat ? number_format($trx->total_berat, 2) . ' kg' : '-' }}</td>
                        <td class="py-3.5 px-6 font-bold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <a href="{{ route('nasabah.riwayat.show', $trx) }}" class="text-xs text-emerald-600 font-semibold hover:underline">Lihat Rincian</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400 text-xs">Belum ada catatan mutasi tabungan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(method_exists($transaksiList, 'hasPages') && $transaksiList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transaksiList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

