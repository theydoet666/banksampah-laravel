@extends('layouts.app')

@section('title', 'Unit Bank Sampah')
@section('breadcrumb', 'Master Data > Unit Bank Sampah')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Unit Bank Sampah (Dusun / RW)</h2>
            <p class="text-xs text-slate-500">Kelola cabang unit operasional bank sampah di tingkat dusun dan rukun warga</p>
        </div>
        <div>
            <a href="{{ route('admin.units.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Unit Baru
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('admin.units.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode unit, atau ketua..." class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.units.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Kode Unit</th>
                        <th class="py-3 px-4">Nama Unit</th>
                        <th class="py-3 px-4">Penanggung Jawab</th>
                        <th class="py-3 px-4">Kontak / No HP</th>
                        <th class="py-3 px-4 text-center">Nasabah</th>
                        <th class="py-3 px-4 text-center">Transaksi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($units as $unit)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">
                                {{ $unit->kode_unit }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.units.show', $unit) }}" class="font-semibold text-slate-800 hover:text-emerald-600">
                                    {{ $unit->nama }}
                                </a>
                                @if($unit->alamat)
                                    <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $unit->alamat }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                {{ $unit->nama_ketua ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $unit->no_hp ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                {{ number_format($unit->nasabah_count) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                                {{ number_format($unit->transaksi_count) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($unit->status === 'aktif')
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
                                <a href="{{ route('admin.units.show', $unit) }}" class="p-1.5 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 inline-block" title="Detail Unit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.units.edit', $unit) }}" class="p-1.5 text-amber-600 hover:text-amber-700 rounded-lg hover:bg-amber-50 inline-block" title="Edit Unit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" 
                                        @click="$dispatch('open-confirm-modal', {
                                            title: 'Ubah Status Unit',
                                            message: 'Apakah Anda yakin ingin mengubah status unit {{ $unit->nama }} menjadi {{ $unit->status === 'aktif' ? 'nonaktif' : 'aktif' }}?',
                                            action: '{{ route('admin.units.toggle', $unit) }}',
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
                            <td colspan="8">
                                <x-empty-state 
                                    message="Belum ada unit bank sampah" 
                                    submessage="Tambahkan unit dusun/RW pertama Anda untuk mulai mengelola bank sampah multi unit." 
                                    actionText="Tambah Unit Baru" 
                                    :actionUrl="route('admin.units.create')" 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

