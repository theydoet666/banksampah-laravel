<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo');
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->boolean('is_saldo_awal')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('is_saldo_awal');
        });

        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropColumn('saldo_awal');
        });
    }
};
