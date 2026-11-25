<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan semua user yang memiliki PIN default '123456' dan set pin menjadi NULL
        // Temukan user yang memiliki password default 'password' atau 'password123' dan set must_change_password = true
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                $updates = [];

                if (! empty($user->pin) && Hash::check('123456', $user->pin)) {
                    $updates['pin'] = null;
                }

                if (Hash::check('password', $user->password) || Hash::check('password123', $user->password)) {
                    $updates['must_change_password'] = true;
                }

                if (! empty($updates)) {
                    $user->forceFill($updates)->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible tidak merestore PIN atau password tidak aman
    }
};
