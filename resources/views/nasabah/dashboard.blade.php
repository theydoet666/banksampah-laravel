@extends('layouts.nasabah')

@section('title', 'Buku Tabungan Nasabah')

@section('content')
<div class="space-y-6">
    <!-- Virtual Card ATM Style -->
    <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-700 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="flex items-center justify-between mb-8">
            <span class="text-xs uppercase tracking-widest font-semibold text-emerald-200">Kartu Tabungan Sampah</span>
            <span class="font-bold text-lg tracking-wider text-emerald-100">{{ appSetting('nama_bank_sampah', 'Bank Sampah BAA') }}</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div class="space-y-1">
                <p class="text-xs text-emerald-200">Saldo Tersedia Ditarik</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $nasabah ? $nasabah->formatted_saldo_tersedia : formatRupiah($saldo) }}</h2>
                @if($nasabah && $nasabah->saldo_tertahan > 0)
                    <p class="text-xs text-amber-200 inline-flex items-center gap-1.5 mt-1 bg-amber-900/30 px-2 py-0.5 rounded-md border border-amber-300/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-ping"></span>
                        Sedang diajukan: {{ $nasabah->formatted_saldo_tertahan }}
                    </p>
                @endif
            </div>

            <a href="{{ route('nasabah.tarik.index') }}" class="px-4 py-2 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-bold inline-flex items-center gap-2 shadow-sm transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tarik Saldo
            </a>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-white/15 text-xs">
            <div>
                <p class="text-emerald-300">Nomor Rekening</p>
                <p class="font-mono text-sm font-semibold tracking-wider">{{ $noRekening }}</p>
            </div>
            <div class="text-right">
                <p class="text-emerald-300">Nama Pemilik</p>
                <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Total Setoran Bulan Ini</p>
                <h4 class="text-xl font-bold text-emerald-600 mt-1">{{ formatRupiah($setorBulanIni) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-400">Total Penarikan Bulan Ini</p>
                <h4 class="text-xl font-bold text-rose-600 mt-1">{{ formatRupiah($tarikBulanIni) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800 text-sm">Riwayat Transaksi Terakhir</h3>
            <a href="{{ route('nasabah.riwayat.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">Lihat Semua</a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($transaksiTerbaru as $trx)
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            {{ $trx->jenis === 'setor' ? '+' : '-' }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 capitalize">{{ $trx->jenis === 'setor' ? 'Setor Sampah' : 'Tarik Saldo' }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->tanggal_transaksi->format('d M Y') }} &bull; {{ $trx->kode_transaksi }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold block {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                        </span>
                        <div class="mt-0.5">{!! $trx->status_badge !!}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-8 text-center">Belum ada riwayat transaksi</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

