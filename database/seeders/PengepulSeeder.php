<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengepul;
use App\Models\PenjualanSampah;
use App\Models\DetailPenjualanSampah;
use App\Models\JenisSampah;
use App\Models\User;
use App\Services\PenjualanService;
use Carbon\Carbon;

class PengepulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pengepuls = [
            [
                'nama_perusahaan' => 'CV Daur Mulia Abadi',
                'penanggung_jawab' => 'Bpk. Bambang Sutrisno',
                'no_hp' => '081289012345',
                'alamat' => 'Kawasan Pergudangan Daur Ulang Blok B-4, Cikarang',
                'status' => 'aktif',
            ],
            [
                'nama_perusahaan' => 'Lapak Berkah Jaya Plastik',
                'penanggung_jawab' => 'H. Syamsuddin',
                'no_hp' => '081398765432',
                'alamat' => 'Jl. Raya Industri Barat No. 88',
                'status' => 'aktif',
            ],
        ];

        $createdPengepul = [];
        foreach ($pengepuls as $p) {
            $createdPengepul[] = Pengepul::firstOrCreate(['nama_perusahaan' => $p['nama_perusahaan']], $p);
        }

        // Buat 2 transaksi penjualan sampel jika ada stok yang mencukupi
        $admin = User::where('role', 'admin')->first();
        $penjualanService = app(PenjualanService::class);
        $stokGudang = $penjualanService->getStokSampahGudang();

        // Cari jenis sampah yang punya sisa stok >= 10 kg
        $readyToSell = $stokGudang->filter(fn($j) => $j['sisa_stok'] >= 10)->values();

        if ($readyToSell->count() >= 2 && !empty($createdPengepul)) {
            $pengepul = $createdPengepul[0];

            $item1 = $readyToSell[0];
            $item2 = $readyToSell[1];

            $beratJual1 = min(15.0, round($item1['sisa_stok'] * 0.4, 1));
            $hargaJual1 = round($item1['harga_beli'] * 1.35 / 100) * 100; // margin 35%
            $subtotal1 = $beratJual1 * $hargaJual1;

            $beratJual2 = min(12.0, round($item2['sisa_stok'] * 0.4, 1));
            $hargaJual2 = round($item2['harga_beli'] * 1.30 / 100) * 100; // margin 30%
            $subtotal2 = $beratJual2 * $hargaJual2;

            $totalBerat = $beratJual1 + $beratJual2;
            $totalNilai = $subtotal1 + $subtotal2;

            $tgl = Carbon::now()->subDays(5);

            $penjualan = PenjualanSampah::create([
                'kode_penjualan' => 'JUAL-' . $tgl->format('Ymd') . '-00001',
                'pengepul_id' => $pengepul->id,
                'tanggal_penjualan' => $tgl->toDateString(),
                'total_berat' => $totalBerat,
                'total_pendapatan' => $totalNilai,
                'status_pembayaran' => 'lunas',
                'pengelola_id' => $admin->id,
                'catatan' => 'Pengiriman perdana hasil sortir gudang desa. Truk B 9123 KDA.',
                'created_at' => $tgl,
                'updated_at' => $tgl,
            ]);

            DetailPenjualanSampah::create([
                'penjualan_id' => $penjualan->id,
                'jenis_sampah_id' => $item1['id'],
                'berat' => $beratJual1,
                'harga_jual_satuan' => $hargaJual1,
                'subtotal' => $subtotal1,
                'created_at' => $tgl,
                'updated_at' => $tgl,
            ]);

            DetailPenjualanSampah::create([
                'penjualan_id' => $penjualan->id,
                'jenis_sampah_id' => $item2['id'],
                'berat' => $beratJual2,
                'harga_jual_satuan' => $hargaJual2,
                'subtotal' => $subtotal2,
                'created_at' => $tgl,
                'updated_at' => $tgl,
            ]);
        }
    }
}
