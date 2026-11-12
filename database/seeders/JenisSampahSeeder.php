<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriSampah;
use App\Models\JenisSampah;

class JenisSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Plastik' => [
                ['nama' => 'Botol PET Bening', 'satuan' => 'kg', 'harga_beli' => 3000, 'harga_jual' => 4200, 'deskripsi' => 'Botol air mineral bersih tanpa tutup'],
                ['nama' => 'Plastik HD / Kresek', 'satuan' => 'kg', 'harga_beli' => 2000, 'harga_jual' => 2800, 'deskripsi' => 'Kantong kresek dan plastik kemasan'],
                ['nama' => 'Plastik PP Gelas', 'satuan' => 'kg', 'harga_beli' => 1500, 'harga_jual' => 2200, 'deskripsi' => 'Gelas plastik minuman bersih'],
            ],
            'Kertas' => [
                ['nama' => 'Kardus / Box', 'satuan' => 'kg', 'harga_beli' => 2500, 'harga_jual' => 3400, 'deskripsi' => 'Kardus coklat tebal dan kering'],
                ['nama' => 'Koran / Majalah', 'satuan' => 'kg', 'harga_beli' => 1500, 'harga_jual' => 2100, 'deskripsi' => 'Koran bekas dan buku majalah'],
                ['nama' => 'Kertas HVS Putih', 'satuan' => 'kg', 'harga_beli' => 2000, 'harga_jual' => 2800, 'deskripsi' => 'Kertas kantor putih tanpa jilid'],
            ],
            'Logam' => [
                ['nama' => 'Besi Tua', 'satuan' => 'kg', 'harga_beli' => 5000, 'harga_jual' => 6500, 'deskripsi' => 'Besi tebal potongan pipa/plat'],
                ['nama' => 'Aluminium Kaleng', 'satuan' => 'kg', 'harga_beli' => 15000, 'harga_jual' => 19500, 'deskripsi' => 'Kaleng minuman aluminium'],
                ['nama' => 'Tembaga', 'satuan' => 'kg', 'harga_beli' => 50000, 'harga_jual' => 62000, 'deskripsi' => 'Kabel tembaga tanpa kulit'],
            ],
            'Kaca' => [
                ['nama' => 'Botol Kaca Utuh', 'satuan' => 'kg', 'harga_beli' => 1000, 'harga_jual' => 1500, 'deskripsi' => 'Botol sirup/kecap/bir utuh'],
            ],
            'Elektronik' => [
                ['nama' => 'Komponen Komputer / PCB', 'satuan' => 'kg', 'harga_beli' => 3000, 'harga_jual' => 4500, 'deskripsi' => 'Motherboard dan kabel bekas'],
            ],
            'Organik' => [
                ['nama' => 'Sampah Sayur & Daun', 'satuan' => 'kg', 'harga_beli' => 500, 'harga_jual' => 800, 'deskripsi' => 'Sisa sayuran dan daun untuk pakan/kompos'],
            ],
        ];

        foreach ($items as $kategoriNama => $jenisList) {
            $kategori = KategoriSampah::where('nama', $kategoriNama)->first();
            if ($kategori) {
                foreach ($jenisList as $item) {
                    JenisSampah::updateOrCreate(
                        ['kategori_id' => $kategori->id, 'nama' => $item['nama']],
                        [
                            'satuan' => $item['satuan'],
                            'harga_beli' => $item['harga_beli'],
                            'harga_jual' => $item['harga_jual'],
                            'harga_per_satuan' => $item['harga_beli'],
                            'deskripsi' => $item['deskripsi'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
