@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('breadcrumb', 'Transaksi / Detail')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">{{ $transaksi->kode_transaksi }}</h2>
                {!! $transaksi->status_badge !!}
            </div>
            <p class="text-xs text-slate-400">Dicatat pada {{ $transaksi->tanggal_transaksi->format('d M Y') }} oleh {{ $transaksi->petugas->name ?? '-' }} ({{ $transaksi->unit->nama ?? 'Unit Umum' }})</p>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($transaksi->status, ['aktif', 'disetujui']))
                <a href="{{ roleRoute('transaksi.struk', $transaksi) }}" target="_blank" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Struk
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'pengelola']) && $transaksi->status === 'menunggu_persetujuan')
                <div x-data="{ openApprove: false, pin: '', showPin: false }">
                    <button type="button" @click="openApprove = true; pin = ''" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs inline-flex items-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui Penarikan
                    </button>

                    <!-- Modal Otorisasi PIN -->
                    <div x-show="openApprove" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display: none;" x-cloak>
                        <div @click.away="openApprove = false" class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl text-left space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">Otorisasi Pencairan Kas</h4>
                                    <p class="text-xs text-slate-500">Konfirmasi penarikan tabungan nasabah</p>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Nasabah:</span>
                                    <strong class="text-slate-800">{{ $transaksi->nasabah->nama }}</strong>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Nominal:</span>
                                    <span class="font-black text-rose-600 text-sm">Rp {{ number_format($transaksi->total_nilai, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if(! auth()->user()->hasPin())
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs space-y-2">
                                    <p class="font-semibold">Akun Anda belum memiliki PIN Otorisasi!</p>
                                    <p class="text-[11px] text-amber-700">Untuk keamanan kas, Anda harus membuat 6-digit PIN terlebih dahulu.</p>
                                    <a href="{{ route('profile.edit') }}" class="inline-block text-xs font-bold text-emerald-700 underline">
                                        Buat PIN Sekarang di Profil &rarr;
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.persetujuan-tarik.setujui', $transaksi) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Masukkan PIN Otorisasi (6 Digit) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input :type="showPin ? 'text' : 'password'" 
                                                   name="pin" 
                                                   x-model="pin" 
                                                   maxlength="6" 
                                                   minlength="6" 
                                                   pattern="[0-9]{6}" 
                                                   inputmode="numeric" 
                                                   required 
                                                   placeholder="••••••" 
                                                   class="w-full text-center tracking-[0.5em] text-lg font-mono font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 pr-10 shadow-xs" 
                                                   autocomplete="off" />
                                            <button type="button" @click="showPin = !showPin" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                                <svg x-show="!showPin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <svg x-show="showPin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" @click="openApprove = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                            Batal
                                        </button>
                                        <button type="submit" :disabled="pin.length !== 6" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white disabled:opacity-50 transition-colors shadow-xs">
                                            Konfirmasi & Cairkan
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(auth()->user()->role === 'admin' && in_array($transaksi->status, ['aktif', 'disetujui']))
                <button type="button" 
                        @click="$dispatch('open-confirm-modal', {
                            action: '{{ route('admin.transaksi.void', $transaksi) }}',
                            method: 'POST',
                            title: 'Batalkan (Void) Transaksi',
                            message: 'PERINGATAN: Membatalkan transaksi {{ $transaksi->kode_transaksi }} akan mengoreksi dan mengembalikan saldo nasabah terkait.',
                            confirmText: 'Ya, Void Transaksi',
                            confirmClass: 'bg-rose-600 hover:bg-rose-700 text-white',
                            showInput: true,
                            inputName: 'alasan',
                            inputLabel: 'Alasan Pembatalan (Void)',
                            inputPlaceholder: 'Tuliskan alasan pembatalan transaksi...',
                            inputRequired: false
                        })"
                        class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold">
                    Void Transaksi
                </button>
            @endif
        </div>
    </div>

    <!-- Nasabah Card Info -->
    <x-card title="Informasi Nasabah">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400 block">Nama Nasabah</span>
                <span class="font-bold text-slate-800">{{ $transaksi->nasabah->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Nomor Rekening</span>
                <span class="font-mono text-slate-700 font-semibold">{{ $transaksi->nasabah->no_rekening ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Saldo Terkini</span>
                <span class="font-bold text-emerald-600">{{ $transaksi->nasabah->formatted_saldo ?? '-' }}</span>
            </div>
        </div>
    </x-card>

    @if($transaksi->is_saldo_awal)
        <!-- Saldo Awal Info -->
        <x-card title="Rincian Saldo Awal Migrasi">
            <div class="p-4 rounded-xl bg-sky-50 border border-sky-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs uppercase font-bold text-sky-700 tracking-wider block">Saldo Awal Pembukuan Manual</span>
                    <p class="text-xs text-slate-600 mt-1">Transaksi migrasi saldo sisa nasabah dari pencatatan pembukuan manual lama ke sistem digital.</p>
                    <p class="text-xs text-slate-500 mt-2"><strong>Catatan:</strong> {{ $transaksi->catatan ?: '-' }}</p>
                </div>
                <div class="sm:text-right shrink-0">
                    <span class="text-xs text-slate-400 block uppercase font-medium">Nominal Saldo Awal</span>
                    <span class="text-2xl font-black text-sky-700">{{ formatRupiah($transaksi->total_nilai) }}</span>
                </div>
            </div>
        </x-card>
    @elseif($transaksi->jenis === 'setor')
        <!-- Breakdown Sampah -->
        <x-card title="Rincian Sampah Masuk">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                            <th class="py-2.5">Jenis Sampah</th>
                            <th class="py-2.5">Kategori</th>
                            <th class="py-2.5">Berat</th>
                            <th class="py-2.5">Harga Snapshot</th>
                            <th class="py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaksi->details as $item)
                            <tr>
                                <td class="py-3 font-semibold text-slate-800">{{ $item->jenisSampah->nama ?? '-' }}</td>
                                <td class="py-3 text-xs text-slate-500">{{ $item->jenisSampah->kategori->nama ?? '-' }}</td>
                                <td class="py-3 text-xs font-medium text-slate-700">{{ number_format($item->berat, 2, ',', '.') }} {{ $item->jenisSampah->satuan ?? 'kg' }}</td>
                                <td class="py-3 text-xs text-slate-500">{{ formatRupiah($item->harga_satuan) }}</td>
                                <td class="py-3 text-right font-bold text-emerald-600">{{ formatRupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 font-bold">
                            <td colspan="2" class="py-3 text-slate-700">Total Keseluruhan</td>
                            <td class="py-3 text-slate-700">{{ number_format($transaksi->total_berat, 2, ',', '.') }} kg</td>
                            <td></td>
                            <td class="py-3 text-right text-emerald-700 text-base">{{ formatRupiah($transaksi->total_nilai) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>
    @else
        <x-card title="Rincian Penarikan">
            <div class="p-6 bg-slate-50 rounded-xl text-center space-y-3">
                <span class="text-xs text-slate-400 uppercase font-semibold">Nominal Penarikan Saldo</span>
                <h3 class="text-3xl font-extrabold text-rose-600">{{ formatRupiah($transaksi->total_nilai) }}</h3>
                <div class="inline-flex items-center gap-2 text-xs text-slate-600">
                    <span class="font-semibold">Metode:</span>
                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 uppercase font-bold">{{ str_replace('_', ' ', $transaksi->metode_penarikan ?? 'tunai') }}</span>
                </div>
                <p class="text-xs text-slate-500">Catatan: {{ $transaksi->catatan ?: '-' }}</p>

                @if($transaksi->disetujuiOleh)
                    <div class="mt-4 pt-3 border-t border-slate-200 text-xs text-slate-600">
                        Disetujui & dicairkan oleh <strong class="text-slate-800">{{ $transaksi->disetujuiOleh->name }}</strong> 
                        pada {{ $transaksi->tanggal_persetujuan?->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                @endif

                @if($transaksi->alasan_penolakan)
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700 text-left">
                        <strong>Alasan Penolakan:</strong> {{ $transaksi->alasan_penolakan }}
                    </div>
                @endif
            </div>
        </x-card>
    @endif
</div>
@endsection

