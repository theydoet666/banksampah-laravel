@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('breadcrumb', 'Transaksi / Detail')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">{{ $transaksi->kode_transaksi }}</h2>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $transaksi->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                    {{ ucfirst($transaksi->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400">Dicatat pada {{ $transaksi->tanggal_transaksi->format('d M Y') }} oleh {{ $transaksi->petugas->name ?? '-' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.transaksi.struk', $transaksi) }}" target="_blank" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>

            @if(auth()->user()->role === 'admin' && $transaksi->status === 'aktif')
                <form method="POST" action="{{ route('admin.transaksi.void', $transaksi) }}" onsubmit="return confirm('PERINGATAN: Membatalkan transaksi ini akan mengoreksi kembali saldo nasabah. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold">
                        Void Transaksi
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Nasabah Card Info -->
    <x-card title="Informasi Nasabah">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400 block">Nama Nasabah</span>
                <span class="font-bold text-slate-800">{{ $transaksi->nasabah->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Nomor Rekening</span>
                <span class="font-mono text-slate-700 font-semibold">{{ $transaksi->nasabah->no_rekening ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Saldo Terkini</span>
                <span class="font-bold text-emerald-600">{{ $transaksi->nasabah->formatted_saldo ?? '-' }}</span>
            </div>
        </div>
    </x-card>

    @if($transaksi->jenis === 'setor')
        <!-- Breakdown Sampah -->
        <x-card title="Rincian Sampah Masuk">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                            <th class="py-2.5">Jenis Sampah</th>
                            <th class="py-2.5">Kategori</th>
                            <th class="py-2.5">Berat</th>
                            <th class="py-2.5">Harga Snapshot</th>
                            <th class="py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaksi->details as $item)
                            <tr>
                                <td class="py-3 font-semibold text-slate-800">{{ $item->jenisSampah->nama ?? '-' }}</td>
                                <td class="py-3 text-xs text-slate-500">{{ $item->jenisSampah->kategori->nama ?? '-' }}</td>
                                <td class="py-3 text-xs font-medium text-slate-700">{{ number_format($item->berat, 2, ',', '.') }} {{ $item->jenisSampah->satuan ?? 'kg' }}</td>
                                <td class="py-3 text-xs text-slate-500">{{ formatRupiah($item->harga_satuan) }}</td>
                                <td class="py-3 text-right font-bold text-emerald-600">{{ formatRupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 font-bold">
                            <td colspan="2" class="py-3 text-slate-700">Total Keseluruhan</td>
                            <td class="py-3 text-slate-700">{{ number_format($transaksi->total_berat, 2, ',', '.') }} kg</td>
                            <td></td>
                            <td class="py-3 text-right text-emerald-700 text-base">{{ formatRupiah($transaksi->total_nilai) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>
    @else
        <x-card title="Rincian Penarikan">
            <div class="p-6 bg-slate-50 rounded-xl text-center space-y-2">
                <span class="text-xs text-slate-400 uppercase font-semibold">Nominal Yang Ditarik Tunai</span>
                <h3 class="text-3xl font-extrabold text-rose-600">{{ formatRupiah($transaksi->total_nilai) }}</h3>
                <p class="text-xs text-slate-500">Catatan: {{ $transaksi->catatan ?: '-' }}</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

