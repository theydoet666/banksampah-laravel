@extends('layouts.app')

@section('title', 'Petugas Dashboard')
@section('breadcrumb', 'Dashboard Petugas')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <x-stat-card 
            title="Transaksi Anda Hari Ini" 
            :value="$transaksiHariIni . ' Transaksi'" 
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card 
            title="Total Transaksi Bulan Ini" 
            :value="$transaksiBulanIni . ' Transaksi'" 
            color="yellow"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />

        <x-stat-card 
            title="Nilai Dikelola Bulan Ini" 
            :value="formatRupiah($nilaiTransaksiBulanIni)" 
            color="green"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <!-- Quick Actions Card -->
    <x-card title="Aksi Cepat Loket">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('petugas.transaksi.setor.create') }}" class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-4 transition-colors">
                <div class="w-12 h-12 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Setor Sampah</h4>
                    <p class="text-xs text-slate-500">Timbang dan tabung sampah</p>
                </div>
            </a>

            <a href="{{ route('petugas.transaksi.tarik.create') }}" class="p-4 rounded-xl border border-sky-200 bg-sky-50 hover:bg-sky-100 flex items-center gap-4 transition-colors">
                <div class="w-12 h-12 rounded-lg bg-sky-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Tarik Saldo</h4>
                    <p class="text-xs text-slate-500">Pencairan tabungan tunai</p>
                </div>
            </a>

            <a href="{{ route('petugas.nasabah.index') }}" class="p-4 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 flex items-center gap-4 transition-colors">
                <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Buku Nasabah</h4>
                    <p class="text-xs text-slate-500">Cari nomor rekening & saldo</p>
                </div>
            </a>
        </div>
    </x-card>
</div>
@endsection

