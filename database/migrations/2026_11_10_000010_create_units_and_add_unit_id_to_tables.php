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
        // 1. Buat tabel units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 20)->unique();
            $table->string('nama', 100);
            $table->text('alamat')->nullable();
            $table->string('nama_ketua', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // 2. Tambah kolom unit_id ke tabel users (Petugas Unit, null jika Pengelola Desa/Admin)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('role')->constrained('units')->nullOnDelete();
        });

        // 3. Tambah kolom unit_id ke tabel nasabah
        Schema::table('nasabah', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('user_id')->constrained('units')->cascadeOnDelete();
        });

        // 4. Tambah kolom unit_id ke tabel transaksi
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('nasabah_id')->constrained('units')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::dropIfExists('units');
    }
};

