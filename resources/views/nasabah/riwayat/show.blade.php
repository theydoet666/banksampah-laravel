@extends('layouts.nasabah')

@section('title', 'Rincian Transaksi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Detail Transaksi</h2>
            <p class="text-xs font-mono text-slate-400">{{ $transaksi->kode_transaksi }}</p>
        </div>
        <a href="{{ route('nasabah.riwayat.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">&larr; Kembali ke Riwayat</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 space-y-6">
        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-100 text-sm">
            <div>
                <span class="text-xs text-slate-400 block">Tanggal Transaksi</span>
                <span class="font-semibold text-slate-800">{{ $transaksi->tanggal_transaksi->format('d F Y') }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Petugas Loket</span>
                <span class="font-semibold text-slate-800">{{ $transaksi->petugas->name ?? '-' }}</span>
            </div>
        </div>

        @if($transaksi->jenis === 'setor')
            <div>
                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Item Sampah Yang Ditimbang</h4>
                <div class="divide-y divide-slate-100">
                    @foreach($transaksi->details as $item)
                        <div class="py-2.5 flex items-center justify-between text-sm">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $item->jenisSampah->nama ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ number_format($item->berat, 2) }} {{ $item->jenisSampah->satuan ?? 'kg' }} &times; {{ formatRupiah($item->harga_satuan) }}</p>
                            </div>
                            <span class="font-bold text-emerald-600">{{ formatRupiah($item->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-400 block">Total Sampah</span>
                    <span class="font-bold text-slate-800">{{ number_format($transaksi->total_berat, 2) }} kg</span>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Total Ditambahkan ke Saldo</span>
                    <span class="text-xl font-extrabold text-emerald-600">{{ formatRupiah($transaksi->total_nilai) }}</span>
                </div>
            </div>
        @else
            <div class="text-center py-6 bg-slate-50 rounded-xl space-y-2">
                <span class="text-xs text-slate-400 uppercase font-semibold">Jumlah Penarikan Tunai</span>
                <h3 class="text-3xl font-extrabold text-rose-600">{{ formatRupiah($transaksi->total_nilai) }}</h3>
                <p class="text-xs text-slate-500">Catatan: {{ $transaksi->catatan ?: '-' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

