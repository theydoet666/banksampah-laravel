@extends('layouts.app')

@section('title', 'Detail Unit - ' . $unit->nama)
@section('breadcrumb', 'Master Data > Unit Bank Sampah > ' . $unit->nama)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-mono font-bold text-lg flex items-center justify-center shadow-md shadow-emerald-900/20">
                {{ $unit->kode_unit }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $unit->nama }}</h2>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span>Ketua: <strong class="text-slate-600">{{ $unit->nama_ketua ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>HP: <strong class="text-slate-600">{{ $unit->no_hp ?? '-' }}</strong></span>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.units.edit', $unit) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                Edit Unit
            </a>
            <a href="{{ route('admin.units.index') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Stat Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nasabah Terdaftar</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($unit->nasabah_count) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($unit->transaksi_count) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Ditugaskan</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($unit->users_count) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Petugas & Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Posko & Petugas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs space-y-4">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Unit</h4>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block">Alamat Pos Penimbangan:</span>
                    <p class="font-medium text-slate-700 mt-0.5">{{ $unit->alamat ?? 'Belum diisi' }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block mb-1.5">Daftar Petugas Unit Ini:</span>
                    @forelse($petugasList as $petugas)
                        <div class="flex items-center justify-between py-1.5 text-xs text-slate-700">
                            <span class="font-semibold">{{ $petugas->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $petugas->email }}</span>
                        </div>
                    @empty
                        <p class="text-slate-400 italic text-xs">Belum ada petugas yang ditugaskan ke unit ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Transactions in this Unit -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">10 Transaksi Terakhir di Unit Ini</h4>
                <a href="{{ route('admin.transaksi.index', ['unit_id' => $unit->id]) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Lihat Semua &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-4">Kode Transaksi</th>
                            <th class="py-2.5 px-4">Nasabah</th>
                            <th class="py-2.5 px-4 text-center">Jenis</th>
                            <th class="py-2.5 px-4 text-right">Nilai (Rp)</th>
                            <th class="py-2.5 px-4">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentTransactions as $trx)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-2.5 px-4 font-mono font-medium text-emerald-700">
                                    <a href="{{ route('admin.transaksi.show', $trx) }}" class="hover:underline">
                                        {{ $trx->kode_transaksi }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-4 font-medium text-slate-800">
                                    {{ $trx->nasabah->nama ?? '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ ucfirst($trx->jenis) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-semibold text-slate-800">
                                    {{ formatRupiah($trx->total_nilai) }}
                                </td>
                                <td class="py-2.5 px-4 text-slate-400 text-[11px]">
                                    {{ $trx->tanggal_transaksi->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 italic">
                                    Belum ada transaksi di unit ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

