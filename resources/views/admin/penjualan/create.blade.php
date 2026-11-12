@extends('layouts.app')

@section('title', 'Catat Penjualan Sampah ke Pengepul')
@section('breadcrumb', 'Penjualan > Catat Penjualan Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Catat Penjualan Sampah ke Pengepul</h2>
            <p class="text-xs text-slate-500">Pencatatan pengiriman sampah fisik dari gudang desa ke mitra pembeli</p>
        </div>
        <a href="{{ route('admin.penjualan.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
            &larr; Kembali
        </a>
    </div>

    @if($availableJenis->isEmpty())
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-6 rounded-2xl text-center space-y-2">
            <h4 class="font-bold text-sm">Gudang Sampah Desa Kosong</h4>
            <p class="text-xs text-amber-600">Saat ini tidak ada stok sampah yang terkumpul dari setoran warga untuk dijual ke pengepul.</p>
            <div class="pt-2">
                <a href="{{ route('admin.transaksi.setor.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl">
                    Input Setoran Warga
                </a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('admin.penjualan.store') }}" 
              x-data="penjualanForm()" 
              x-init="init()" 
              @submit="submitting = true" 
              class="space-y-6">
            @csrf

            <!-- Header Info -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Pengepul & Pengiriman</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mitra Pengepul <span class="text-rose-500">*</span></label>
                        <select name="pengepul_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Pilih Pengepul...</option>
                            @foreach($pengepulList as $p)
                                <option value="{{ $p->id }}" {{ old('pengepul_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_perusahaan }} ({{ $p->penanggung_jawab ?? 'PIC' }})
                                </option>
                            @endforeach
                        </select>
                        @error('pengepul_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengiriman <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_penjualan" value="{{ old('tanggal_penjualan', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="status_pembayaran" required class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="lunas" {{ old('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas (Tunai/Transfer)</option>
                            <option value="piutang" {{ old('status_pembayaran') === 'piutang' ? 'selected' : '' }}>Piutang / Tempo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Nomor plat truk, sopir penjemput, nomor DO pengepul..." class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Sampah yang Dijual</h3>
                        <p class="text-[11px] text-slate-400">Pilih jenis sampah dan masukkan timbangan yang diangkut pengepul</p>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase text-[10px]">
                                <th class="py-2.5 px-2">Jenis Sampah (Tersedia)</th>
                                <th class="py-2.5 px-2 w-36">Berat Jual (kg)</th>
                                <th class="py-2.5 px-2 w-44">Harga Jual / kg (Rp)</th>
                                <th class="py-2.5 px-2 text-right w-40">Subtotal</th>
                                <th class="py-2.5 px-2 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="py-2.5 px-2">
                                        <select :name="`details[${index}][jenis_sampah_id]`" 
                                                x-model="item.jenis_sampah_id" 
                                                @change="onJenisChange(index)" 
                                                required 
                                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">Pilih Jenis Sampah...</option>
                                            <template x-for="j in availableJenis" :key="j.id">
                                                <option :value="j.id" x-text="`${j.nama} (Stok: ${formatNumber(j.sisa_stok)} kg | Std Jual: Rp ${formatRupiah(j.harga_jual)})`"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <input type="number" 
                                               :name="`details[${index}][berat]`" 
                                               x-model.number="item.berat" 
                                               @input="calculateSubtotal(index)" 
                                               step="0.01" 
                                               min="0.01" 
                                               :max="item.maxStok" 
                                               placeholder="0.0" 
                                               required 
                                               class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                                        <span x-show="item.maxStok > 0" class="text-[10px] text-slate-400 block mt-0.5">Maks: <span x-text="formatNumber(item.maxStok)"></span> kg</span>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <input type="number" 
                                               :name="`details[${index}][harga_jual_satuan]`" 
                                               x-model.number="item.harga_jual_satuan" 
                                               @input="calculateSubtotal(index)" 
                                               step="100" 
                                               min="100" 
                                               placeholder="Harga / kg" 
                                               required 
                                               class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-bold text-slate-800">
                                        Rp <span x-text="formatRupiah(item.subtotal)"></span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 font-bold bg-slate-50/50">
                                <td class="py-3 px-2 text-right uppercase text-[11px] text-slate-500">Total Keseluruhan:</td>
                                <td class="py-3 px-2 text-slate-800 font-mono text-xs">
                                    <span x-text="formatNumber(totalBerat)"></span> kg
                                </td>
                                <td></td>
                                <td class="py-3 px-2 text-right text-emerald-700 text-sm font-mono">
                                    Rp <span x-text="formatRupiah(totalPendapatan)"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.penjualan.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" x-bind:disabled="submitting || totalBerat <= 0" class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 rounded-xl shadow-xs transition-colors">
                    <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Simpan Transaksi Penjualan'">Simpan Transaksi Penjualan</span>
                </button>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
function penjualanForm() {
    return {
        availableJenis: @json($availableJenis),
        items: [],
        submitting: false,
        totalBerat: 0,
        totalPendapatan: 0,

        init() {
            this.addItem();
        },

        addItem() {
            this.items.push({
                jenis_sampah_id: '',
                berat: '',
                harga_jual_satuan: '',
                subtotal: 0,
                maxStok: 0
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateGrandTotal();
            }
        },

        onJenisChange(index) {
            const selectedId = this.items[index].jenis_sampah_id;
            const jenis = this.availableJenis.find(j => j.id == selectedId);
            if (jenis) {
                this.items[index].maxStok = parseFloat(jenis.sisa_stok);
                // Auto-fill harga jual standar master komoditas
                this.items[index].harga_jual_satuan = jenis.harga_jual ? parseFloat(jenis.harga_jual) : Math.round(parseFloat(jenis.harga_beli) * 1.3 / 100) * 100;
            } else {
                this.items[index].maxStok = 0;
            }
            this.calculateSubtotal(index);
        },

        calculateSubtotal(index) {
            const berat = parseFloat(this.items[index].berat) || 0;
            const harga = parseFloat(this.items[index].harga_jual_satuan) || 0;
            this.items[index].subtotal = Math.round(berat * harga);
            this.calculateGrandTotal();
        },

        calculateGrandTotal() {
            this.totalBerat = this.items.reduce((sum, item) => sum + (parseFloat(item.berat) || 0), 0);
            this.totalPendapatan = this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
        },

        formatNumber(num) {
            return Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        },

        formatRupiah(num) {
            return Number(num || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush
@endsection

