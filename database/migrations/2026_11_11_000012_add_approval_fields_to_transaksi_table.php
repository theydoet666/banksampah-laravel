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
        Schema::table('transaksi', function (Blueprint $table) {
            // Ubah tipe status menjadi string agar mendukung status persetujuan
            $table->string('status', 30)->default('disetujui')->change();
            
            $table->foreignId('disetujui_oleh')->nullable()->after('petugas_id')->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_persetujuan')->nullable()->after('disetujui_oleh');
            $table->text('alasan_penolakan')->nullable()->after('tanggal_persetujuan');
            $table->string('metode_penarikan', 50)->nullable()->default('tunai')->after('catatan');
        });

        // Update data lama: jika status 'aktif', ubah menjadi 'disetujui'
        DB::table('transaksi')->where('status', 'aktif')->update(['status' => 'disetujui']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn(['disetujui_oleh', 'tanggal_persetujuan', 'alasan_penolakan', 'metode_penarikan']);
        });
    }
};

