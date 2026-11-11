@extends('layouts.nasabah')

@section('title', 'Tarik Saldo Tabungan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tarik Saldo Tabungan</h1>
            <p class="text-xs text-slate-500">Ajukan pencairan saldo sampah Anda untuk dikonfirmasi oleh pengelola desa</p>
        </div>
        <a href="{{ route('nasabah.dashboard') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <!-- Ringkasan Saldo Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-5 shadow-sm">
            <p class="text-xs text-emerald-100 font-medium">Total Saldo Terkumpul</p>
            <h3 class="text-2xl font-black mt-1">{{ $nasabah->formatted_saldo }}</h3>
            <p class="text-[10px] text-emerald-200 mt-2">Akumulasi setoran sampah Anda</p>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
            <p class="text-xs text-slate-400 font-semibold uppercase">Saldo Tersedia Ditarik</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $nasabah->formatted_saldo_tersedia }}</h3>
            <p class="text-[10px] text-slate-400 mt-2">Bebas ditarik hari ini</p>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <p class="text-xs text-amber-700 font-semibold uppercase">Sedang Menunggu Persetujuan</p>
            <h3 class="text-2xl font-black text-amber-900 mt-1">{{ $nasabah->formatted_saldo_tertahan }}</h3>
            <p class="text-[10px] text-amber-600 mt-2">Dalam proses verifikasi pengelola</p>
        </div>
    </div>

    <!-- Form Pengajuan -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6" x-data="{ nominal: '', saldoTersedia: {{ $nasabah->saldo_tersedia }}, submitting: false }">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">
            Formulir Pengajuan Penarikan
        </h3>

        @if($nasabah->saldo_tersedia < 1000)
            <div class="p-4 bg-amber-50 text-amber-800 rounded-xl text-xs">
                Saldo tersedia Anda saat ini belum mencukupi untuk melakukan penarikan (minimal Rp 1.000). Silakan lakukan setoran sampah di unit dusun/RW terdekat untuk menambah saldo Anda.
            </div>
        @else
            <form method="POST" action="{{ route('nasabah.tarik.store') }}" @submit="submitting = true" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Penarikan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="nominal" x-model="nominal" min="1000" :max="saldoTersedia" required placeholder="Contoh: 50000" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                        <div class="flex items-center justify-between mt-1 text-[11px]">
                            <span class="text-slate-400">Minimal Rp 1.000</span>
                            <button type="button" @click="nominal = saldoTersedia" class="text-emerald-600 font-semibold hover:underline">Tarik Semua</button>
                        </div>
                        @error('nominal') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pengambilan <span class="text-rose-500">*</span></label>
                        <select name="metode_penarikan" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="tunai_pos">Tunai di Pos Unit Dusun/RW ({{ $nasabah->unit->nama ?? 'Unit' }})</option>
                            <option value="tunai_desa">Tunai di Kantor Bank Sampah Desa</option>
                            <option value="transfer">Transfer Rekening / E-Wallet</option>
                        </select>
                        @error('metode_penarikan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="catatan" placeholder="Nomor rekening transfer atau keterangan lain..." class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" :disabled="!nominal || nominal <= 0 || nominal > saldoTersedia || submitting" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs inline-flex items-center gap-2 shadow-xs disabled:opacity-50 transition-colors">
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="submitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Penarikan'">Kirim Pengajuan Penarikan</span>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Riwayat Pengajuan Nasabah -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Pengajuan Penarikan Saya</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-semibold">
                        <th class="py-3 px-4">Tanggal / Kode</th>
                        <th class="py-3 px-4">Nominal</th>
                        <th class="py-3 px-4">Metode & Catatan</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayatPengajuan as $trx)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $trx->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">{{ $trx->kode_transaksi }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-rose-600 text-sm">
                                Rp {{ number_format($trx->total_nilai, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $trx->metode_penarikan ?? 'tunai') }}
                                </span>
                                @if($trx->catatan)
                                    <p class="text-[11px] text-slate-500 mt-1 italic">"{{ $trx->catatan }}"</p>
                                @endif
                                @if($trx->alasan_penolakan)
                                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">Alasan penolakan: {{ $trx->alasan_penolakan }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                {!! $trx->status_badge !!}
                                @if($trx->status === 'disetujui')
                                    <span class="block text-[10px] text-emerald-600 mt-0.5">Dana telah dicairkan</span>
                                @elseif($trx->status === 'menunggu_persetujuan')
                                    <span class="block text-[10px] text-amber-600 mt-0.5">Menunggu konfirmasi desa</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                Belum ada riwayat pengajuan penarikan saldo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayatPengajuan->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $riwayatPengajuan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
