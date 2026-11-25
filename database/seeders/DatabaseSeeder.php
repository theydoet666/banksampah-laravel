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
            AppSettingSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([
                NasabahSeeder::class,
                DemoSeeder::class,
                PengepulSeeder::class,
            ]);
        }
    }
}
