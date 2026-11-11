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
            UserSeeder::class,
            KategoriSampahSeeder::class,
            JenisSampahSeeder::class,
            NasabahSeeder::class,
            AppSettingSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
