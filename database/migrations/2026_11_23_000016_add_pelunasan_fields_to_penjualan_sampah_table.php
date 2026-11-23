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
        Schema::table('penjualan_sampah', function (Blueprint $table) {
            $table->date('tanggal_pelunasan')->nullable()->after('status_pembayaran');
            $table->string('metode_pelunasan', 50)->nullable()->after('tanggal_pelunasan');
            $table->string('catatan_pelunasan', 255)->nullable()->after('metode_pelunasan');
            $table->foreignId('diterima_oleh')->nullable()->after('catatan_pelunasan')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_sampah', function (Blueprint $table) {
            $table->dropForeign(['diterima_oleh']);
            $table->dropColumn([
                'tanggal_pelunasan',
                'metode_pelunasan',
                'catatan_pelunasan',
                'diterima_oleh',
            ]);
        });
    }
};
