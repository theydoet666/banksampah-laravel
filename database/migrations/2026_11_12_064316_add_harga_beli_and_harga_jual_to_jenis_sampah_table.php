<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->decimal('harga_beli', 10, 2)->default(0)->after('satuan');
            $table->decimal('harga_jual', 10, 2)->default(0)->after('harga_beli');
        });

        // Isi data awal dari harga_per_satuan yang sudah ada
        DB::statement("UPDATE jenis_sampah SET harga_beli = harga_per_satuan, harga_jual = ROUND(harga_per_satuan * 1.30, -1)");

        Schema::table('harga_sampah_log', function (Blueprint $table) {
            $table->string('tipe_perubahan', 20)->default('beli')->after('jenis_sampah_id');
            $table->decimal('harga_beli_lama', 10, 2)->nullable()->after('harga_baru');
            $table->decimal('harga_beli_baru', 10, 2)->nullable()->after('harga_beli_lama');
            $table->decimal('harga_jual_lama', 10, 2)->nullable()->after('harga_beli_baru');
            $table->decimal('harga_jual_baru', 10, 2)->nullable()->after('harga_jual_lama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harga_sampah_log', function (Blueprint $table) {
            $table->dropColumn(['tipe_perubahan', 'harga_beli_lama', 'harga_beli_baru', 'harga_jual_lama', 'harga_jual_baru']);
        });

        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->dropColumn(['harga_beli', 'harga_jual']);
        });
    }
};
