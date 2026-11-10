@extends('layouts.app')

@section('title', 'Log Perubahan Harga')
@section('breadcrumb', 'Master Data / Jenis Sampah / Riwayat Harga')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Perubahan Harga</h2>
            <p class="text-xs text-slate-500 font-semibold text-emerald-600">{{ $jenisSampah->nama }} (Kategori: {{ $jenisSampah->kategori->nama ?? '-' }})</p>
        </div>
        <a href="{{ route('admin.jenis-sampah.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-3 px-6">Tanggal & Waktu</th>
                    <th class="py-3 px-6">Harga Lama</th>
                    <th class="py-3 px-6">Harga Baru</th>
                    <th class="py-3 px-6 text-right">Diubah Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="py-3.5 px-6 text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3.5 px-6 font-medium text-slate-400">{{ formatRupiah($log->harga_lama) }}</td>
                        <td class="py-3.5 px-6 font-bold text-emerald-600">{{ formatRupiah($log->harga_baru) }}</td>
                        <td class="py-3.5 px-6 text-right text-xs text-slate-700 font-medium">{{ $log->user->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-slate-400 text-xs">Belum ada riwayat perubahan harga</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

