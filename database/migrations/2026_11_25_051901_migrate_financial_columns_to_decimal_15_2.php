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
        // 1. Jenis Sampah: ubah kolom harga ke DECIMAL(15,2)
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->decimal('harga_per_satuan', 15, 2)->change();
            if (Schema::hasColumn('jenis_sampah', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->default(0)->change();
            }
            if (Schema::hasColumn('jenis_sampah', 'harga_jual')) {
                $table->decimal('harga_jual', 15, 2)->default(0)->change();
            }
        });

        // 2. Log Perubahan Harga
        if (Schema::hasTable('harga_sampah_log')) {
            Schema::table('harga_sampah_log', function (Blueprint $table) {
                $table->decimal('harga_lama', 15, 2)->change();
                $table->decimal('harga_baru', 15, 2)->change();
                if (Schema::hasColumn('harga_sampah_log', 'harga_beli_lama')) {
                    $table->decimal('harga_beli_lama', 15, 2)->nullable()->change();
                }
                if (Schema::hasColumn('harga_sampah_log', 'harga_beli_baru')) {
                    $table->decimal('harga_beli_baru', 15, 2)->nullable()->change();
                }
                if (Schema::hasColumn('harga_sampah_log', 'harga_jual_lama')) {
                    $table->decimal('harga_jual_lama', 15, 2)->nullable()->change();
                }
                if (Schema::hasColumn('harga_sampah_log', 'harga_jual_baru')) {
                    $table->decimal('harga_jual_baru', 15, 2)->nullable()->change();
                }
            });
        }

        // 3. Detail Transaksi
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
        });

        // 4. Detail Penjualan Sampah
        if (Schema::hasTable('detail_penjualan_sampah')) {
            Schema::table('detail_penjualan_sampah', function (Blueprint $table) {
                $table->decimal('harga_jual_satuan', 15, 2)->change();
                $table->decimal('subtotal', 15, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ke 10,2 jika diperlukan
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->decimal('harga_per_satuan', 10, 2)->change();
            if (Schema::hasColumn('jenis_sampah', 'harga_beli')) {
                $table->decimal('harga_beli', 10, 2)->default(0)->change();
            }
            if (Schema::hasColumn('jenis_sampah', 'harga_jual')) {
                $table->decimal('harga_jual', 10, 2)->default(0)->change();
            }
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->decimal('harga_satuan', 10, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
        });
    }
};
