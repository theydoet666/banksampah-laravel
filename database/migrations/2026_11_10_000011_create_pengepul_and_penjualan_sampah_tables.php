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
        // 1. Tabel Master Pengepul (Mitra Off-taker Daur Ulang)
        Schema::create('pengepul', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan', 100);
            $table->string('penanggung_jawab', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // 2. Tabel Master Transaksi Penjualan Sampah (Level Desa)
        Schema::create('penjualan_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan', 30)->unique();
            $table->foreignId('pengepul_id')->constrained('pengepul')->cascadeOnDelete();
            $table->date('tanggal_penjualan');
            $table->decimal('total_berat', 10, 3);
            $table->decimal('total_pendapatan', 15, 2);
            $table->enum('status_pembayaran', ['lunas', 'piutang'])->default('lunas');
            $table->foreignId('pengelola_id')->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Detail Item Penjualan Sampah
        Schema::create('detail_penjualan_sampah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan_sampah')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampah');
            $table->decimal('berat', 10, 3);
            $table->decimal('harga_jual_satuan', 10, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan_sampah');
        Schema::dropIfExists('penjualan_sampah');
        Schema::dropIfExists('pengepul');
    }
};
