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
        User::updateOrCreate(
            ['email' => 'admin@banksampah.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@banksampah.test'],
            [
                'name' => 'Petugas Satu',
                'password' => Hash::make('password'),
                'role' => 'petugas',
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
    }
}

