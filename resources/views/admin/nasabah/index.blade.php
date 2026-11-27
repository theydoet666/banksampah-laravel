@extends('layouts.app')

@section('title', 'Data Nasabah')
@section('breadcrumb', 'Master Data / Nasabah')

@section('content')
<div class="space-y-6" x-data="{ showExportModal: false, showPin: false }">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Daftar Nasabah</h2>
            <p class="text-xs text-slate-500">Kelola anggota masyarakat penabung sampah</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            @if(auth()->user()->isAdmin())
                <button type="button" @click="showExportModal = true" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm shadow-xs transition-colors w-full sm:w-auto">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Akun &amp; Password
                </button>
            @endif
            <a href="{{ roleRoute('nasabah.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-colors w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Nasabah
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <x-card>
        <form method="GET" action="{{ roleRoute('nasabah.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama, nomor rekening, atau no. HP..." class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            @if(auth()->user()->isAdmin())
                <div>
                    <select name="unit_id" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex items-center gap-2 {{ auth()->user()->isPetugas() ? 'sm:col-span-2' : '' }}">
                <select name="status" class="flex-1 min-w-0 rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold shrink-0 shadow-xs">Cari</button>
            </div>
        </form>
    </x-card>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 overflow-hidden w-full">
        @if($nasabahList->isEmpty())
            <x-empty-state 
                message="Tidak ada nasabah yang ditemukan" 
                submessage="Silakan periksa kata kunci pencarian Anda atau tambahkan nasabah baru." 
                actionText="Tambah Nasabah Baru" 
                :actionUrl="roleRoute('nasabah.create')" 
            />
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse text-sm min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">No. Rekening</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama & NIK</th>
                            <th class="py-3.5 px-4 sm:px-6">Unit</th>
                            <th class="py-3.5 px-4 sm:px-6">No. HP</th>
                            <th class="py-3.5 px-4 sm:px-6">Saldo</th>
                            <th class="py-3.5 px-4 sm:px-6">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($nasabahList as $nasabah)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4 sm:px-6 font-mono font-medium text-slate-800 whitespace-nowrap">{{ $nasabah->no_rekening }}</td>
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="font-semibold text-slate-800">{{ $nasabah->nama }}</div>
                                    <div class="text-xs text-slate-400">NIK: {{ $nasabah->nik ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    @if($nasabah->unit)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                            {{ $nasabah->unit->nama }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Pusat / Tanpa Unit</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-slate-600 whitespace-nowrap">{{ $nasabah->no_hp }}</td>
                                <td class="py-4 px-4 sm:px-6 font-semibold text-emerald-600 whitespace-nowrap">{{ $nasabah->formatted_saldo }}</td>
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $nasabah->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($nasabah->status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ roleRoute('nasabah.show', $nasabah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ roleRoute('nasabah.edit', $nasabah) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <button type="button" 
                                                    @click="$dispatch('open-confirm-modal', {
                                                        action: '{{ route('admin.nasabah.toggle-status', $nasabah) }}',
                                                        method: 'PATCH',
                                                        title: 'Ubah Status Nasabah',
                                                        message: 'Apakah Anda yakin ingin {{ $nasabah->status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan' }} nasabah {{ $nasabah->nama }}?',
                                                        confirmText: '{{ $nasabah->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}',
                                                        confirmClass: '{{ $nasabah->status === 'aktif' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}'
                                                    })"
                                                    class="p-1.5 rounded-lg {{ $nasabah->status === 'aktif' ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}" 
                                                    title="{{ $nasabah->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        @endif
                                        @if(auth()->user()->isAdmin() || auth()->user()->isPetugas())
                                            <button type="button"
                                                    @click="$dispatch('open-confirm-modal', {
                                                        action: '{{ roleRoute('nasabah.destroy', $nasabah) }}',
                                                        method: 'DELETE',
                                                        title: 'Hapus Nasabah',
                                                        message: 'Apakah Anda yakin ingin menghapus nasabah {{ addslashes($nasabah->nama) }} ({{ $nasabah->no_rekening }})? Tindakan ini hanya berlaku untuk nasabah yang belum memiliki saldo atau riwayat transaksi.',
                                                        confirmText: 'Ya, Hapus',
                                                        confirmClass: 'bg-rose-600 hover:bg-rose-700 text-white'
                                                    })"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50"
                                                    title="Hapus Nasabah">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($nasabahList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $nasabahList->links() }}
            </div>
        @endif
    </div>
    @if(auth()->user()->isAdmin())
        <!-- Modal Export Akun & Password Sementara (Khusus Admin + PIN) -->
        <div x-show="showExportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div @click.away="showExportModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Export Rekap Akun &amp; Password Nasabah</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Hanya menampilkan kata sandi sementara untuk nasabah yang belum pernah login/ganti password.</p>
                    </div>
                    <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.nasabah.export-credentials') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Filter Unit Bank Sampah</label>
                        <select name="unit_id" class="w-full rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 text-xs text-slate-700">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="only_pending" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Hanya nasabah yang belum ganti password</span>
                        </label>
                        <label class="inline-flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="regenerate_missing" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                            <span>Generate ulang password sementara bagi nasabah lama yang belum memilikinya</span>
                        </label>
                    </div>

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5">
                        <label class="block text-xs font-bold text-amber-900">PIN Otorisasi Administrator (6 Digit) *</label>
                        <div class="relative">
                            <input :type="showPin ? 'text' : 'password'" name="pin" maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" required placeholder="••••••" autocomplete="off" class="w-full text-center tracking-[0.4em] font-mono font-bold text-sm rounded-lg border-amber-300 focus:border-amber-500 focus:ring-amber-500 pr-9">
                            <button type="button" @click="showPin = !showPin" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-amber-800">Aktivitas export akan dicatat pada Audit Log sistem.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs">Unduh Excel (.xlsx)</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

