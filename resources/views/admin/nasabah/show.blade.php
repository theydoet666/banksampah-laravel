@extends('layouts.app')

@section('title', 'Detail Nasabah')
@section('breadcrumb', 'Master Data / Nasabah / Detail')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ $nasabah->nama }}</h2>
            <p class="text-xs text-slate-500 font-mono">No. Rekening: {{ $nasabah->no_rekening }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.nasabah.edit', $nasabah) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-xs">Edit Profil</a>
            <a href="{{ route('admin.transaksi.setor.create', ['nasabah_id' => $nasabah->id]) }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">Setor Sampah</a>
            <a href="{{ route('admin.transaksi.tarik.create', ['nasabah_id' => $nasabah->id]) }}" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-xs">Tarik Saldo</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <x-card title="Informasi Identitas">
            <div class="space-y-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">NIK</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->nik ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Jenis Kelamin</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tempat, Tanggal Lahir</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->tempat_lahir ?? '-' }}, {{ $nasabah->tanggal_lahir ? $nasabah->tanggal_lahir->format('d M Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">No. HP / WhatsApp</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->no_hp }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->alamat }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 block">Status Akun</span>
                        <span class="font-semibold {{ $nasabah->status === 'aktif' ? 'text-emerald-600' : 'text-slate-400' }}">{{ ucfirst($nasabah->status) }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Terdaftar Oleh</span>
                        <span class="font-medium text-slate-700">{{ $nasabah->petugasPendaftar->name ?? 'Sistem' }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Saldo & Transaksi List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gradient-to-r from-emerald-800 to-teal-700 rounded-2xl p-6 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase tracking-wider text-emerald-200">Saldo Tabungan Saat Ini</span>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $nasabah->formatted_saldo }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Riwayat Transaksi -->
            <x-card title="Riwayat 10 Transaksi Terakhir">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                                <th class="py-2.5">Kode Transaksi</th>
                                <th class="py-2.5">Tanggal</th>
                                <th class="py-2.5">Jenis</th>
                                <th class="py-2.5">Nilai (Rp)</th>
                                <th class="py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($nasabah->transaksi as $trx)
                                <tr>
                                    <td class="py-3 font-mono text-xs font-semibold text-slate-700">{{ $trx->kode_transaksi }}</td>
                                    <td class="py-3 text-xs text-slate-500">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                                    <td class="py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                            {{ ucfirst($trx->jenis) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-semibold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="text-xs {{ $trx->status === 'aktif' ? 'text-slate-600' : 'text-rose-400 line-through' }}">
                                            {{ ucfirst($trx->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Belum ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

