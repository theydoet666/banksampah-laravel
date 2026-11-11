@extends('layouts.app')

@section('title', 'Jenis Sampah')
@section('breadcrumb', 'Master Data / Jenis Sampah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Katalog Jenis Sampah</h2>
            <p class="text-xs text-slate-500">Harga beli komoditas daur ulang per satuan berat</p>
        </div>
        <a href="{{ route('admin.jenis-sampah.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Sampah
        </a>
    </div>

    <!-- Filter Dropdown -->
    <x-card>
        <form method="GET" action="{{ route('admin.jenis-sampah.index') }}" class="flex items-center gap-4">
            <select name="kategori_id" onchange="this.form.submit()" class="rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kategori)
                    <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama }}
                    </option>
                @endforeach
            </select>
            @if(request('kategori_id'))
                <a href="{{ route('admin.jenis-sampah.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Reset Filter</a>
            @endif
        </form>
    </x-card>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-3.5 px-6">Nama Sampah</th>
                    <th class="py-3.5 px-6">Kategori</th>
                    <th class="py-3.5 px-6">Satuan</th>
                    <th class="py-3.5 px-6">Harga Beli</th>
                    <th class="py-3.5 px-6">Status</th>
                    <th class="py-3.5 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($jenisSampahList as $sampah)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-4 px-6 font-semibold text-slate-800">{{ $sampah->nama }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                {{ $sampah->kategori->nama ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-500 uppercase">{{ $sampah->satuan }}</td>
                        <td class="py-4 px-6 font-bold text-emerald-600">
                            {{ formatRupiah($sampah->harga_per_satuan) }} / {{ $sampah->satuan }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sampah->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $sampah->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.jenis-sampah.harga-log', $sampah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50" title="Riwayat Harga">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </a>
                                <a href="{{ route('admin.jenis-sampah.edit', $sampah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" 
                                        @click="$dispatch('open-confirm-modal', {
                                            action: '{{ route('admin.jenis-sampah.toggle', $sampah) }}',
                                            method: 'PATCH',
                                            title: 'Ubah Status Jenis Sampah',
                                            message: 'Apakah Anda yakin ingin {{ $sampah->is_active ? 'menonaktifkan' : 'mengaktifkan' }} jenis sampah {{ $sampah->nama }}?',
                                            confirmText: '{{ $sampah->is_active ? 'Nonaktifkan' : 'Aktifkan' }}',
                                            confirmClass: '{{ $sampah->is_active ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}'
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50" 
                                        title="{{ $sampah->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <x-empty-state 
                                message="Belum ada jenis sampah ditemukan" 
                                submessage="Tambahkan jenis komoditas sampah dan tentukan harga beli per satuan beratnya." 
                                actionText="Tambah Jenis Sampah" 
                                :actionUrl="route('admin.jenis-sampah.create')" 
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($jenisSampahList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $jenisSampahList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

