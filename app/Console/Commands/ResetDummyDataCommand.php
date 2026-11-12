<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class ResetDummyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank-sampah:reset-data {--keep-pengepul : Tetap pertahankan master data pengepul}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus seluruh data dummy transaksi, penjualan gudang, dan nasabah untuk pengujian dari awal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan data dummy Bank Sampah...');

        Schema::disableForeignKeyConstraints();

        // 1. Hapus riwayat transaksi penjualan sampah & gudang
        DB::table('detail_penjualan_sampah')->truncate();
        DB::table('penjualan_sampah')->truncate();
        $this->line('✓ Tabel penjualan sampah gudang & detail berhasil dikosongkan.');

        // 2. Hapus riwayat transaksi setor & tarik nasabah
        DB::table('detail_transaksi')->truncate();
        DB::table('transaksi')->truncate();
        $this->line('✓ Tabel transaksi setor & tarik nasabah berhasil dikosongkan.');

        // 3. Hapus data nasabah & akun user nasabah dummy
        DB::table('nasabah')->truncate();
        $deletedUsers = User::where('role', 'nasabah')->delete();
        $this->line("✓ Tabel nasabah dikosongkan & $deletedUsers akun user nasabah dummy dibersihkan.");

        // 4. Bersihkan log harga jika ada
        if (Schema::hasTable('harga_sampah_log')) {
            DB::table('harga_sampah_log')->truncate();
        }

        // 5. Pengepul (opsional)
        if (! $this->option('keep-pengepul')) {
            DB::table('pengepul')->truncate();
            $this->line('✓ Tabel pengepul dikosongkan.');
        } else {
            $this->line('ℹ Master data pengepul tetap dipertahankan.');
        }

        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info('====================================================');
        $this->info(' SUKSES: Data dummy transaksi & nasabah telah bersih! ');
        $this->info('====================================================');
        $this->line('Master data yang tetap aktif:');
        $this->line('- Akun Pengguna Inti: Admin, Pengelola Bank Sampah, Petugas');
        $this->line('- Master Kategori & Jenis Sampah');
        $this->line('- Master Unit Dusun');
        $this->line('- Pengaturan Identitas & Logo Bank Sampah');
        $this->newLine();
        $this->line('Nomor urut rekening dan transaksi berikutnya akan dimulai dari urutan #00001.');

        return Command::SUCCESS;
    }
}
