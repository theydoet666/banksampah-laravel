<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriSampah;

class KategoriSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            ['nama' => 'Plastik', 'deskripsi' => 'Berbagai jenis plastik daur ulang'],
            ['nama' => 'Kertas', 'deskripsi' => 'Kertas, kardus, koran, dan dokumen bekas'],
            ['nama' => 'Logam', 'deskripsi' => 'Besi, aluminium, tembaga, dan logam lainnya'],
            ['nama' => 'Kaca', 'deskripsi' => 'Botol kaca, pecahan kaca, dan toples kaca'],
            ['nama' => 'Elektronik', 'deskripsi' => 'Barang elektronik bekas (e-waste)'],
            ['nama' => 'Organik', 'deskripsi' => 'Sisa makanan, daun, dan sampah kompos'],
        ];

        foreach ($kategori as $item) {
            KategoriSampah::updateOrCreate(['nama' => $item['nama']], $item);
        }
    }
}

