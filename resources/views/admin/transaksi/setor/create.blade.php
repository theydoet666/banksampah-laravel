@extends('layouts.app')

@section('title', 'Transaksi Setor Sampah')
@section('breadcrumb', 'Transaksi / Setor Sampah')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="setoranForm()">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Form Setor Sampah</h2>
            <p class="text-xs text-slate-500">Penimbangan dan konversi rupiah langsung ke saldo nasabah</p>
        </div>
        <a href="{{ route('admin.transaksi.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.transaksi.setor.store') }}" class="space-y-6">
        @csrf

        <!-- Informasi Nasabah & Transaksi -->
        <x-card title="Data Nasabah & Waktu Transaksi">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Pilih Nasabah *</label>
                    <select name="nasabah_id" x-model="selectedNasabahId" @change="updateNasabahInfo()" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Nasabah Terdaftar --</option>
                        @foreach($nasabahList as $nasabah)
                            <option value="{{ $nasabah->id }}" data-saldo="{{ $nasabah->saldo }}" data-rekening="{{ $nasabah->no_rekening }}">
                                {{ $nasabah->nama }} ({{ $nasabah->no_rekening }})
                            </option>
                        @endforeach
                    </select>

                    <div x-show="selectedNasabahInfo" class="mt-3 p-3 bg-slate-50 rounded-lg text-xs space-y-1">
                        <p class="text-slate-500">No. Rekening: <span class="font-mono font-bold text-slate-800" x-text="selectedNasabahInfo.rekening"></span></p>
                        <p class="text-slate-500">Saldo Saat Ini: <span class="font-bold text-emerald-600" x-text="formatRupiah(selectedNasabahInfo.saldo)"></span></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Catatan (Opsional)</label>
                        <input type="text" name="catatan" placeholder="Keterangan tambahan transaksi..." class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Detail Sampah Dinamis -->
        <x-card title="Detail Sampah yang Disetor">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                            <th class="py-2.5 w-5/12">Jenis Sampah</th>
                            <th class="py-2.5 w-2/12">Berat</th>
                            <th class="py-2.5 w-2/12">Harga / Satuan</th>
                            <th class="py-2.5 w-2/12">Subtotal</th>
                            <th class="py-2.5 w-1/12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="py-3 pr-3">
                                    <select :name="`details[${index}][jenis_sampah_id]`" x-model="row.jenis_sampah_id" @change="onJenisSampahChange(index)" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Pilih Jenis Sampah</option>
                                        <template x-for="item in jenisSampahList" :key="item.id">
                                            <option :value="item.id" x-text="`${item.nama} (${formatRupiah(item.harga_per_satuan)}/${item.satuan})`"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="py-3 pr-3">
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0.01" :name="`details[${index}][berat]`" x-model="row.berat" @input="calculateRow(index)" required class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0.0">
                                        <span class="absolute right-3 top-2.5 text-xs text-slate-400 uppercase" x-text="row.satuan || 'kg'"></span>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 text-xs font-semibold text-slate-600" x-text="formatRupiah(row.harga_satuan)"></td>
                                <td class="py-3 pr-3 font-bold text-emerald-600" x-text="formatRupiah(row.subtotal)"></td>
                                <td class="py-3 text-center">
                                    <button type="button" @click="removeRow(index)" :disabled="rows.length <= 1" class="p-1 rounded text-slate-400 hover:text-rose-600 disabled:opacity-30">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" @click="addRow()" class="px-3.5 py-2 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-xs font-semibold inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris Sampah
                </button>

                <div class="text-right space-y-1">
                    <p class="text-xs text-slate-400">Total Berat: <span class="font-bold text-slate-700" x-text="totalBerat.toFixed(2) + ' kg'"></span></p>
                    <p class="text-lg font-extrabold text-emerald-700">Total Nilai: <span x-text="formatRupiah(totalNilai)"></span></p>
                </div>
            </div>
        </x-card>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.transaksi.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold">Batal</a>
            <button type="submit" :disabled="!selectedNasabahId || totalNilai <= 0" class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm disabled:opacity-50">
                Simpan & Tambah Saldo
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function setoranForm() {
    return {
        selectedNasabahId: '{{ $selectedNasabahId ?? "" }}',
        selectedNasabahInfo: null,
        jenisSampahList: @json($jenisSampahList),
        rows: [
            { jenis_sampah_id: '', berat: '', harga_satuan: 0, satuan: 'kg', subtotal: 0 }
        ],
        totalBerat: 0,
        totalNilai: 0,

        init() {
            if (this.selectedNasabahId) {
                this.$nextTick(() => this.updateNasabahInfo());
            }
        },

        updateNasabahInfo() {
            const select = document.querySelector('select[name="nasabah_id"]');
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                this.selectedNasabahInfo = {
                    rekening: selectedOption.dataset.rekening,
                    saldo: parseFloat(selectedOption.dataset.saldo || 0)
                };
            } else {
                this.selectedNasabahInfo = null;
            }
        },

        onJenisSampahChange(index) {
            const row = this.rows[index];
            const item = this.jenisSampahList.find(i => i.id == row.jenis_sampah_id);
            if (item) {
                row.harga_satuan = parseFloat(item.harga_per_satuan);
                row.satuan = item.satuan;
            } else {
                row.harga_satuan = 0;
                row.satuan = 'kg';
            }
            this.calculateRow(index);
        },

        calculateRow(index) {
            const row = this.rows[index];
            const berat = parseFloat(row.berat) || 0;
            row.subtotal = berat * (row.harga_satuan || 0);
            this.calculateTotals();
        },

        addRow() {
            this.rows.push({ jenis_sampah_id: '', berat: '', harga_satuan: 0, satuan: 'kg', subtotal: 0 });
        },

        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
                this.calculateTotals();
            }
        },

        calculateTotals() {
            this.totalBerat = this.rows.reduce((sum, r) => sum + (parseFloat(r.berat) || 0), 0);
            this.totalNilai = this.rows.reduce((sum, r) => sum + (r.subtotal || 0), 0);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

