<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'nama_bank_sampah' => 'Bank Sampah BAA',
            'alamat' => 'Jl. Lingkungan Hidup No. 1, Kota Bersih',
            'no_telp' => '08123456789',
            'email' => 'info@banksampah.test',
            'logo' => null,
            'tahun_berdiri' => '2020',
        ];

        foreach ($settings as $key => $value) {
            AppSetting::set($key, $value);
        }
    }
}

