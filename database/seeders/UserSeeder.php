<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            User::updateOrCreate(
                ['email' => 'admin@banksampah.test'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('password'),
                    'pin' => Hash::make('123456'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['email' => 'pengelola@banksampah.test'],
                [
                    'name' => 'Pengelola & Bendahara',
                    'password' => Hash::make('password'),
                    'pin' => Hash::make('123456'),
                    'role' => 'pengelola',
                    'is_active' => true,
                ]
            );

            $firstUnit = \App\Models\Unit::first();

            User::updateOrCreate(
                ['email' => 'petugas@banksampah.test'],
                [
                    'name' => 'Petugas Satu',
                    'password' => Hash::make('password'),
                    'role' => 'petugas',
                    'unit_id' => $firstUnit?->id,
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['email' => 'nasabah@banksampah.test'],
                [
                    'name' => 'Budi Santoso',
                    'password' => Hash::make('password'),
                    'role' => 'nasabah',
                    'is_active' => true,
                ]
            );
        } else {
            // Lingkungan non-local / production: Buat HANYA SATU Administrator awal
            $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@banksampah.id');
            $adminPassword = env('INITIAL_ADMIN_PASSWORD');

            if (empty($adminPassword)) {
                $adminPassword = \App\Support\RandomPassword::generate(16);
                if (isset($this->command)) {
                    $this->command->warn('------------------------------------------------------------');
                    $this->command->warn('PERHATIAN: Akun Administrator Awal Dibuat dengan Sandi Acak:');
                    $this->command->info("Email   : {$adminEmail}");
                    $this->command->info("Password: {$adminPassword}");
                    $this->command->warn('Simpan kredensial ini segera! Anda wajib menggantinya saat login pertama.');
                    $this->command->warn('------------------------------------------------------------');
                }
            }

            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Administrator Utama',
                    'password' => Hash::make($adminPassword),
                    'pin' => null, // PIN harus dibuat sendiri oleh admin via profil
                    'role' => 'admin',
                    'is_active' => true,
                    'must_change_password' => true,
                ]
            );
        }
    }
}

