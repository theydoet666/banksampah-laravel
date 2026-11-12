<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $nasabahsWithoutUser = Nasabah::whereNull('user_id')->get();

        foreach ($nasabahsWithoutUser as $nasabah) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $nasabah->no_hp);
            $defaultPassword = strlen($cleanPhone) >= 6 ? substr($cleanPhone, -6) : '123456';
            $generatedEmail = strtolower(str_replace('-', '', $nasabah->no_rekening)) . '@nasabah.banksampah.test';

            // Pastikan email belum terpakai
            $count = 1;
            $email = $generatedEmail;
            while (User::where('email', $email)->exists()) {
                $email = strtolower(str_replace('-', '', $nasabah->no_rekening)) . "_{$count}@nasabah.banksampah.test";
                $count++;
            }

            $user = User::create([
                'name' => $nasabah->nama,
                'email' => $email,
                'password' => Hash::make($defaultPassword),
                'role' => 'nasabah',
                'is_active' => true,
                'unit_id' => $nasabah->unit_id,
            ]);

            $nasabah->update(['user_id' => $user->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};

