@extends('layouts.app')

@section('title', 'Mitra Pengepul')
@section('breadcrumb', 'Master Data > Mitra Pengepul')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mitra Pengepul (Off-taker Daur Ulang)</h2>
            <p class="text-xs text-slate-500">Kelola daftar pabrik, lapak, dan pembeli grosir sampah desa</p>
        </div>
        <div>
            <a href="{{ route('admin.pengepul.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengepul Baru
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Perusahaan / Lapak</th>
                        <th class="py-3 px-4">Penanggung Jawab</th>
                        <th class="py-3 px-4">No. Kontak / HP</th>
                        <th class="py-3 px-4">Alamat Gudang</th>
                        <th class="py-3 px-4 text-center">Total Transaksi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($pengepulList as $pengepul)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $pengepul->nama_perusahaan }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                {{ $pengepul->penanggung_jawab ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">
                                {{ $pengepul->no_hp ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $pengepul->alamat ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                {{ number_format($pengepul->penjualan_count) }} kali
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($pengepul->status === 'aktif')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('admin.pengepul.edit', $pengepul) }}" class="p-1.5 text-amber-600 hover:text-amber-700 rounded-lg hover:bg-amber-50 inline-block" title="Edit Pengepul">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" 
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Ubah Status Mitra',
                                            message: 'Apakah Anda yakin ingin mengubah status mitra {{ $pengepul->nama_perusahaan }} menjadi {{ $pengepul->status === 'aktif' ? 'nonaktif' : 'aktif' }}?',
                                            action: '{{ route('admin.pengepul.toggle', $pengepul) }}',
                                            method: 'PATCH',
                                            confirmText: 'Ya, Ubah Status',
                                            confirmButtonClass: 'bg-amber-600 hover:bg-amber-700'
                                        })"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                                        title="Toggle Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    message="Belum ada mitra pengepul" 
                                    submessage="Tambahkan mitra lapak atau pabrik daur ulang untuk mencatat penjualan sampah desa." 
                                    actionText="Tambah Pengepul Baru" 
                                    :actionUrl="route('admin.pengepul.create')" 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengepulList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pengepulList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
