<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Nasabah;

class NasabahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petugas = User::where('role', 'petugas')->first() ?? User::where('role', 'admin')->first();
        $userNasabah = User::where('email', 'nasabah@banksampah.test')->first();

        // 1 Nasabah terhubung ke User Budi Santoso
        if ($userNasabah) {
            Nasabah::updateOrCreate(
                ['user_id' => $userNasabah->id],
                [
                    'no_rekening' => 'BS-2026-00001',
                    'nama' => $userNasabah->name,
                    'nik' => '3201123456780001',
                    'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '1990-05-15',
                    'jenis_kelamin' => 'L',
                    'alamat' => 'Jl. Mawar No. 12 RT 01/RW 04, Kelurahan Bersih',
                    'no_hp' => '081234567890',
                    'saldo' => 50000,
                    'status' => 'aktif',
                    'registered_by' => $petugas->id,
                ]
            );
        }

        $demoNasabah = [
            [
                'no_rekening' => 'BS-2026-00002',
                'nama' => 'Siti Aminah',
                'nik' => '3201123456780002',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1985-08-20',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Melati No. 5 RT 02/RW 04',
                'no_hp' => '081298765432',
                'saldo' => 75000,
                'status' => 'aktif',
            ],
            [
                'no_rekening' => 'BS-2026-00003',
                'nama' => 'Agus Setiawan',
                'nik' => '3201123456780003',
                'tempat_lahir' => 'Semarang',
                'tanggal_lahir' => '1992-11-10',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Cempaka No. 8 RT 03/RW 04',
                'no_hp' => '082155554444',
                'saldo' => 32000,
                'status' => 'aktif',
            ],
            [
                'no_rekening' => 'BS-2026-00004',
                'nama' => 'Dewi Lestari',
                'nik' => '3201123456780004',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1995-02-28',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Kenanga No. 19 RT 01/RW 05',
                'no_hp' => '087788889999',
                'saldo' => 120000,
                'status' => 'aktif',
            ],
            [
                'no_rekening' => 'BS-2026-00005',
                'nama' => 'Rahmat Hidayat',
                'nik' => '3201123456780005',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '1988-09-05',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Anggrek No. 3 RT 04/RW 05',
                'no_hp' => '085611112222',
                'saldo' => 0,
                'status' => 'nonaktif',
            ],
        ];

        foreach ($demoNasabah as $nasabah) {
            Nasabah::updateOrCreate(
                ['no_rekening' => $nasabah['no_rekening']],
                array_merge($nasabah, ['registered_by' => $petugas->id])
            );
        }
    }
}

