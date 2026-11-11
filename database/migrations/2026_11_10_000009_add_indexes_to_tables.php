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
            $table->index('status');
            $table->index('registered_by');
            $table->index(['status', 'created_at']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->index('jenis');
            $table->index('status');
            $table->index('tanggal_transaksi');
            $table->index(['nasabah_id', 'tanggal_transaksi']);
            $table->index(['status', 'tanggal_transaksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['jenis']);
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_transaksi']);
            $table->dropIndex(['nasabah_id', 'tanggal_transaksi']);
            $table->dropIndex(['status', 'tanggal_transaksi']);
        });

        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['registered_by']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};

