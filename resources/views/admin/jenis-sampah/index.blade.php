@extends('layouts.app')

@section('title', 'Jenis Sampah')
@section('breadcrumb', 'Master Data / Jenis Sampah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Katalog Jenis Sampah</h2>
            <p class="text-xs text-slate-500">Master harga beli setoran nasabah & harga jual standar ke pengepul</p>
        </div>
        <a href="{{ route('admin.jenis-sampah.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Sampah
        </a>
    </div>

    <!-- Filter Dropdown & Info Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card class="md:col-span-1">
            <form method="GET" action="{{ route('admin.jenis-sampah.index') }}" class="flex items-center gap-3">
                <select name="kategori_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
                @if(request('kategori_id'))
                    <a href="{{ route('admin.jenis-sampah.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 shrink-0">Reset</a>
                @endif
            </form>
        </x-card>

        <div class="md:col-span-2 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-150 rounded-2xl p-3.5 flex items-center justify-between text-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-600/10 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                    💡
                </div>
                <div>
                    <span class="font-bold text-slate-800 block">Struktur Dua Harga (Beli & Jual)</span>
                    <span class="text-[11px] text-slate-500"><b>Harga Beli:</b> Nilai saldo bagi nasabah warga. <b>Harga Jual:</b> Patokan jual ke pengepul untuk margin kas bank sampah.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Nama Sampah</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Satuan</th>
                        <th class="py-3.5 px-4">Harga Beli (Nasabah)</th>
                        <th class="py-3.5 px-4">Harga Jual (Pengepul)</th>
                        <th class="py-3.5 px-4">Margin Laba</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jenisSampahList as $sampah)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-5 font-semibold text-slate-800">
                                {{ $sampah->nama }}
                                @if($sampah->deskripsi)
                                    <span class="block text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $sampah->deskripsi }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                    {{ $sampah->kategori->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 uppercase font-mono font-medium">{{ $sampah->satuan }}</td>
                            <td class="py-3.5 px-4 font-bold text-emerald-700 font-mono">
                                {{ formatRupiah($sampah->harga_beli) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-sky-700 font-mono">
                                {{ formatRupiah($sampah->harga_jual) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($sampah->margin_rp > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        +{{ formatRupiah($sampah->margin_rp) }}
                                        <span class="text-[9px] text-emerald-600">({{ $sampah->margin_persen }}%)</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Rp 0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sampah->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $sampah->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.jenis-sampah.harga-log', $sampah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50" title="Riwayat Harga">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.jenis-sampah.edit', $sampah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.jenis-sampah.toggle', $sampah) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50" title="{{ $sampah->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400 text-xs">Belum ada data jenis sampah</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jenisSampahList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $jenisSampahList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
