<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            UserSeeder::class,
            KategoriSampahSeeder::class,
            JenisSampahSeeder::class,
            NasabahSeeder::class,
            AppSettingSeeder::class,
            DemoSeeder::class,
            UnitSeeder::class,
            PengepulSeeder::class,
        ]);
    }
}
