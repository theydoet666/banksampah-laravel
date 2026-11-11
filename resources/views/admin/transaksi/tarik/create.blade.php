@extends('layouts.app')

@section('title', 'Transaksi Penarikan Saldo')
@section('breadcrumb', 'Transaksi / Tarik Saldo')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="tarikForm()">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Form Tarik Saldo</h2>
            <p class="text-xs text-slate-500">Pencairan tabungan saldo nasabah menjadi uang tunai</p>
        </div>
        <a href="{{ route('admin.transaksi.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.transaksi.tarik.store') }}" class="space-y-5" @submit="submitting = true">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Pilih Nasabah *</label>
                <select name="nasabah_id" x-model="selectedNasabahId" @change="updateNasabahInfo()" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih Nasabah --</option>
                    @foreach($nasabahList as $nasabah)
                        <option value="{{ $nasabah->id }}" data-saldo="{{ $nasabah->saldo }}" data-rekening="{{ $nasabah->no_rekening }}">
                            {{ $nasabah->nama }} (Saldo: {{ $nasabah->formatted_saldo }})
                        </option>
                    @endforeach
                </select>
                @error('nasabah_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror

                <div x-show="saldoAktif > 0" class="mt-3 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                    <span class="text-xs text-emerald-800 font-medium">Saldo Tersedia:</span>
                    <span class="text-base font-bold text-emerald-700" x-text="formatRupiah(saldoAktif)"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nominal Penarikan (Rp) *</label>
                <input type="number" name="nominal" x-model="nominal" min="1000" :max="saldoAktif" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: 50000">
                <div class="flex items-center justify-between mt-1 text-xs">
                    <span class="text-slate-400">Minimal penarikan Rp 1.000</span>
                    <button type="button" @click="nominal = saldoAktif" class="text-emerald-600 font-semibold hover:underline" x-show="saldoAktif > 0">Tarik Semua Saldo</button>
                </div>
                <p x-show="nominal > saldoAktif" class="text-xs text-rose-500 mt-1 font-semibold">Nominal melebihi saldo nasabah saat ini!</p>
                @error('nominal') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tanggal Transaksi *</label>
                <input type="date" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Catatan / Alasan Penarikan</label>
                <textarea name="catatan" rows="2" placeholder="Keterangan opsional..." class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.transaksi.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold">Batal</a>
                <button type="submit" :disabled="!selectedNasabahId || nominal <= 0 || nominal > saldoAktif || submitting" class="px-6 py-2.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow-sm disabled:opacity-50 inline-flex items-center gap-2">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Memproses...' : 'Proses Tarik Tunai'"></span>
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
function tarikForm() {
    return {
        selectedNasabahId: '{{ $selectedNasabahId ?? "" }}',
        saldoAktif: 0,
        nominal: '',
        submitting: false,

        init() {
            if (this.selectedNasabahId) {
                this.$nextTick(() => this.updateNasabahInfo());
            }
        },

        updateNasabahInfo() {
            const select = document.querySelector('select[name="nasabah_id"]');
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                this.saldoAktif = parseFloat(selectedOption.dataset.saldo || 0);
            } else {
                this.saldoAktif = 0;
            }
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

