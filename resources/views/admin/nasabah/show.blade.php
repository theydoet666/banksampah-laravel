@extends('layouts.app')

@section('title', 'Detail Nasabah')
@section('breadcrumb', 'Master Data / Nasabah / Detail')

@section('content')
<div class="space-y-6" x-data="{ showSaldoAwalModal: false }">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ $nasabah->nama }}</h2>
            <p class="text-xs text-slate-500 font-mono">No. Rekening: {{ $nasabah->no_rekening }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="showSaldoAwalModal = true" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-xs inline-flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Saldo Awal
            </button>
            @endif
            <a href="{{ roleRoute('nasabah.edit', $nasabah) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-xs">Edit Profil</a>
            <a href="{{ roleRoute('transaksi.setor.create', ['nasabah_id' => $nasabah->id]) }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">Setor Sampah</a>
            <a href="{{ roleRoute('transaksi.tarik.create', ['nasabah_id' => $nasabah->id]) }}" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs">Tarik Saldo</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <x-card title="Informasi Identitas">
            <div class="space-y-4 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">NIK</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->nik ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Jenis Kelamin</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tempat, Tanggal Lahir</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->tempat_lahir ?? '-' }}, {{ $nasabah->tanggal_lahir ? $nasabah->tanggal_lahir->format('d M Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">No. HP / WhatsApp</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->no_hp }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat</span>
                    <span class="font-medium text-slate-800">{{ $nasabah->alamat }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 block">Status Akun</span>
                        <span class="font-semibold {{ $nasabah->status === 'aktif' ? 'text-emerald-600' : 'text-slate-400' }}">{{ ucfirst($nasabah->status) }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Terdaftar Oleh</span>
                        <span class="font-medium text-slate-700">{{ $nasabah->petugasPendaftar->name ?? 'Sistem' }}</span>
                    </div>
                </div>

                <!-- Akses Login Mandiri -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Akses Buku Tabungan Digital</span>
                    <p class="text-xs text-slate-500">Nasabah dapat login mandiri di HP menggunakan No. Rekening atau No. HP.</p>
                    
                    <form method="POST" action="{{ roleRoute('nasabah.reset-password', $nasabah) }}" onsubmit="return confirm('Reset password akun nasabah {{ $nasabah->nama }} ke default (6 digit No. HP)?');">
                        @csrf
                        <button type="submit" class="w-full mt-1 px-3 py-1.5 rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            Reset Password Nasabah
                        </button>
                    </form>
                </div>
            </div>
        </x-card>

        <!-- Saldo & Transaksi List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gradient-to-r from-emerald-800 to-teal-700 rounded-2xl p-6 text-white shadow-md flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase tracking-wider text-emerald-200">Saldo Tabungan Saat Ini</span>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $nasabah->formatted_saldo }}</h3>
                    @if($nasabah->saldo_awal > 0)
                        <p class="text-xs text-emerald-100/90 mt-1.5 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-white/20 text-[10px] font-bold uppercase tracking-wider">Migrasi</span>
                            Termasuk saldo awal pembukuan manual: <span class="font-bold font-mono">{{ formatRupiah($nasabah->saldo_awal) }}</span>
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Riwayat Transaksi -->
            <x-card title="Riwayat 10 Transaksi Terakhir">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                                <th class="py-2.5">Kode Transaksi</th>
                                <th class="py-2.5">Tanggal</th>
                                <th class="py-2.5">Jenis</th>
                                <th class="py-2.5">Nilai (Rp)</th>
                                <th class="py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($nasabah->transaksi as $trx)
                                <tr>
                                    <td class="py-3 font-mono text-xs font-semibold text-slate-700">
                                        {{ $trx->kode_transaksi }}
                                        @if($trx->catatan)
                                            <span class="block font-sans font-normal text-[11px] text-slate-400 truncate max-w-xs">{{ $trx->catatan }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-xs text-slate-500">{{ $trx->tanggal_transaksi->format('d/m/Y') }}</td>
                                    <td class="py-3">
                                        @if($trx->is_saldo_awal)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                                Saldo Awal
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $trx->jenis === 'setor' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                                {{ ucfirst($trx->jenis) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-semibold {{ $trx->jenis === 'setor' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $trx->jenis === 'setor' ? '+' : '-' }}{{ formatRupiah($trx->total_nilai) }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="text-xs {{ $trx->status === 'aktif' ? 'text-slate-600' : 'text-rose-400 line-through' }}">
                                            {{ ucfirst($trx->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 text-xs">Belum ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <!-- Modal Input Saldo Awal -->
    <div x-show="showSaldoAwalModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showSaldoAwalModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-base">Input Saldo Awal Migrasi</h3>
                            <p class="text-xs text-slate-400">{{ $nasabah->nama }} ({{ $nasabah->no_rekening }})</p>
                        </div>
                    </div>
                    <button type="button" @click="showSaldoAwalModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.nasabah.saldo-awal', $nasabah) }}" class="space-y-4">
                    @csrf
                    <div class="p-3 rounded-lg bg-sky-50 text-xs text-sky-800 border border-sky-100 leading-relaxed">
                        Fitur ini digunakan khusus untuk memasukkan saldo sisa pembukuan manual lama. Saldo nasabah akan bertambah dan dicatat sebagai transaksi Saldo Awal.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nominal Saldo Awal (Rp) *</label>
                        <input type="number" name="nominal" required min="1" step="100" class="w-full rounded-lg border-slate-200 text-base font-bold text-slate-800 focus:border-sky-500 focus:ring-sky-500" placeholder="0">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tanggal Transaksi / Pembukuan *</label>
                        <input type="date" name="tanggal_transaksi" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Catatan / Keterangan (Opsional)</label>
                        <input type="text" name="catatan" value="Saldo awal migrasi pembukuan manual" class="w-full rounded-lg border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500" placeholder="Contoh: Saldo buku tabungan fisik No. 15">
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showSaldoAwalModal = false" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-xs inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Saldo Awal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

