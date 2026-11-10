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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->enum('jenis', ['setor', 'tarik']);
            $table->decimal('total_berat', 10, 3)->nullable();
            $table->decimal('total_nilai', 15, 2);
            $table->text('catatan')->nullable();
            $table->enum('status', ['aktif', 'void'])->default('aktif');
            $table->foreignId('petugas_id')->constrained('users');
            $table->date('tanggal_transaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};

