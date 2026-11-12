@extends('layouts.app')

@section('title', 'Log Perubahan Harga')
@section('breadcrumb', 'Master Data / Jenis Sampah / Riwayat Harga')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Riwayat Perubahan Harga</h2>
            <p class="text-xs text-slate-500 font-semibold text-emerald-600">{{ $jenisSampah->nama }} (Kategori: {{ $jenisSampah->kategori->nama ?? '-' }})</p>
        </div>
        <a href="{{ route('admin.jenis-sampah.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Tipe Perubahan</th>
                        <th class="py-3 px-4">Harga Beli (Nasabah)</th>
                        <th class="py-3 px-4">Harga Jual (Pengepul)</th>
                        <th class="py-3 px-4 text-right">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/40">
                            <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeClass = match($log->tipe_perubahan) {
                                        'jual' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'keduanya' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    };
                                    $label = match($log->tipe_perubahan) {
                                        'jual' => 'Harga Jual',
                                        'keduanya' => 'Beli & Jual',
                                        default => 'Harga Beli',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($log->harga_beli_baru !== null)
                                    <span class="text-slate-400 line-through text-[11px]">{{ formatRupiah($log->harga_beli_lama) }}</span>
                                    <span class="font-bold text-emerald-700 ml-1">&rarr; {{ formatRupiah($log->harga_beli_baru) }}</span>
                                @else
                                    <span class="text-slate-400 line-through text-[11px]">{{ formatRupiah($log->harga_lama) }}</span>
                                    <span class="font-bold text-emerald-700 ml-1">&rarr; {{ formatRupiah($log->harga_baru) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($log->harga_jual_baru !== null && $log->harga_jual_baru > 0)
                                    <span class="text-slate-400 line-through text-[11px]">{{ formatRupiah($log->harga_jual_lama) }}</span>
                                    <span class="font-bold text-sky-700 ml-1">&rarr; {{ formatRupiah($log->harga_jual_baru) }}</span>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-700 font-medium">
                                {{ $log->user->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400 text-xs">Belum ada riwayat perubahan harga</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
