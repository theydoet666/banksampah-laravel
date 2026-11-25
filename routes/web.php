<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NasabahController as AdminNasabahController;
use App\Http\Controllers\Admin\KategoriSampahController as AdminKategoriSampahController;
use App\Http\Controllers\Admin\JenisSampahController as AdminJenisSampahController;
use App\Http\Controllers\Admin\TransaksiController as AdminTransaksiController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PengaturanController as AdminPengaturanController;
use App\Http\Controllers\Admin\UnitController as AdminUnitController;
use App\Http\Controllers\Admin\PengepulController as AdminPengepulController;
use App\Http\Controllers\Admin\PenjualanSampahController as AdminPenjualanSampahController;
use App\Http\Controllers\Admin\PersetujuanPenarikanController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;

// Petugas Controllers
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;

// Nasabah Controllers
use App\Http\Controllers\Nasabah\DashboardController as NasabahDashboardController;
use App\Http\Controllers\Nasabah\RiwayatController as NasabahRiwayatController;
use App\Http\Controllers\Nasabah\ProfilController as NasabahProfilController;
use App\Http\Controllers\Nasabah\NasabahTarikController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isAdmin() || $user->isPengelola()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isPetugas()) {
        return redirect()->route('petugas.dashboard');
    }
    return redirect()->route('nasabah.dashboard');
})->middleware(['auth', 'check.active'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin & Pengelola (Bank Sampah Induk) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,pengelola', 'check.active', 'force.password.change'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Fitur Eksklusif Admin Sistem (Desa)
        Route::middleware('role:admin')->group(function () {
            // Master Data Unit Bank Sampah
            Route::resource('/units', AdminUnitController::class);
            Route::patch('/units/{unit}/toggle', [AdminUnitController::class, 'toggle'])->name('units.toggle');

            // Manajemen Pengguna
            Route::resource('/users', AdminUserController::class)->except(['show', 'destroy']);
            Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
            Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

            // Pengaturan Sistem & Identitas Logo
            Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('pengaturan.index');
            Route::put('/pengaturan', [AdminPengaturanController::class, 'update'])->name('pengaturan.update');

            // Master Kategori & Jenis Sampah (Manajemen Penuh)
            Route::resource('/kategori-sampah', AdminKategoriSampahController::class)->except(['index', 'show']);
            Route::resource('/jenis-sampah', AdminJenisSampahController::class)->except(['index', 'show']);
            Route::patch('/jenis-sampah/{jenisSampah}/toggle', [AdminJenisSampahController::class, 'toggle'])->name('jenis-sampah.toggle');
            Route::get('/jenis-sampah/{jenisSampah}/harga-log', [AdminJenisSampahController::class, 'hargaLog'])->name('jenis-sampah.harga-log');

            // Void Transaksi
            Route::post('/transaksi/{transaksi}/void', [AdminTransaksiController::class, 'void'])
                ->middleware('throttle:15,1')
                ->name('transaksi.void');

            // Toggle Status Nasabah
            Route::patch('/nasabah/{nasabah}/toggle-status', [AdminNasabahController::class, 'toggleStatus'])->name('nasabah.toggle-status');

            // Input Saldo Awal Nasabah (Khusus Admin)
            Route::post('/nasabah/{nasabah}/saldo-awal', [AdminNasabahController::class, 'inputSaldoAwal'])
                ->middleware('throttle:20,1')
                ->name('nasabah.saldo-awal');

            // Audit Log (Khusus Admin)
            Route::get('/audit-log', [AdminAuditLogController::class, 'index'])->name('audit-log.index');
            Route::get('/audit-log/export', [AdminAuditLogController::class, 'export'])->name('audit-log.export');
            Route::get('/audit-log/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-log.show');
        });

        // Master Data Sampah (Dapat Dilihat oleh Admin & Pengelola)
        Route::resource('/kategori-sampah', AdminKategoriSampahController::class)->only(['index', 'show']);
        Route::resource('/jenis-sampah', AdminJenisSampahController::class)->only(['index', 'show']);

        // Data Nasabah
        Route::resource('/nasabah', AdminNasabahController::class);
        Route::post('/nasabah/{nasabah}/reset-password', [AdminNasabahController::class, 'resetPassword'])->name('nasabah.reset-password');

        // Transaksi Warga
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/setor/create', [AdminTransaksiController::class, 'createSetor'])->name('transaksi.setor.create');
        Route::post('/transaksi/setor', [AdminTransaksiController::class, 'storeSetor'])->name('transaksi.setor.store');
        Route::get('/transaksi/tarik/create', [AdminTransaksiController::class, 'createTarik'])->name('transaksi.tarik.create');
        Route::post('/transaksi/tarik', [AdminTransaksiController::class, 'storeTarik'])->name('transaksi.tarik.store');
        Route::get('/transaksi/{transaksi}', [AdminTransaksiController::class, 'show'])->name('transaksi.show');
        Route::get('/transaksi/{transaksi}/struk', [AdminTransaksiController::class, 'struk'])->name('transaksi.struk');

        // Persetujuan Penarikan Saldo (Admin & Pengelola)
        Route::get('/persetujuan-tarik', [PersetujuanPenarikanController::class, 'index'])->name('persetujuan-tarik.index');
        Route::post('/persetujuan-tarik/{transaksi}/setujui', [PersetujuanPenarikanController::class, 'setujui'])
            ->middleware('throttle:15,1')
            ->name('persetujuan-tarik.setujui');
        Route::post('/persetujuan-tarik/{transaksi}/tolak', [PersetujuanPenarikanController::class, 'tolak'])->name('persetujuan-tarik.tolak');

        // Mitra Pengepul (Admin & Pengelola)
        Route::resource('/pengepul', AdminPengepulController::class)->except(['show']);
        Route::patch('/pengepul/{pengepul}/toggle', [AdminPengepulController::class, 'toggle'])->name('pengepul.toggle');

        // Penjualan Sampah ke Pengepul & Gudang Desa (Admin & Pengelola)
        Route::get('/penjualan/stok', [AdminPenjualanSampahController::class, 'stok'])->name('penjualan.stok');
        Route::resource('/penjualan', AdminPenjualanSampahController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/penjualan/{penjualan}/surat-jalan', [AdminPenjualanSampahController::class, 'suratJalan'])->name('penjualan.surat-jalan');
        Route::patch('/penjualan/{penjualan}/pelunasan', [AdminPenjualanSampahController::class, 'pelunasan'])->name('penjualan.pelunasan');

        // Laporan (Admin & Pengelola)
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/excel', [AdminLaporanController::class, 'exportExcel'])->name('laporan.excel');
        Route::get('/laporan/export/pdf', [AdminLaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/laporan/laba-rugi', [AdminLaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/laba-rugi/pdf', [AdminLaporanController::class, 'exportLabaRugiPdf'])->name('laporan.laba-rugi.pdf');
    });

/*
|--------------------------------------------------------------------------
| Petugas (Operator) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas,admin', 'check.active', 'force.password.change'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

        // Nasabah (Manajemen Nasabah Pos Unit)
        Route::resource('/nasabah', AdminNasabahController::class)->except(['destroy']);
        Route::post('/nasabah/{nasabah}/reset-password', [AdminNasabahController::class, 'resetPassword'])->name('nasabah.reset-password');

        // Transaksi Setor & Tarik
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/setor/create', [AdminTransaksiController::class, 'createSetor'])->name('transaksi.setor.create');
        Route::post('/transaksi/setor', [AdminTransaksiController::class, 'storeSetor'])->name('transaksi.setor.store');
        Route::get('/transaksi/tarik/create', [AdminTransaksiController::class, 'createTarik'])->name('transaksi.tarik.create');
        Route::post('/transaksi/tarik', [AdminTransaksiController::class, 'storeTarik'])->name('transaksi.tarik.store');
        Route::get('/transaksi/{transaksi}', [AdminTransaksiController::class, 'show'])->name('transaksi.show');
        Route::get('/transaksi/{transaksi}/struk', [AdminTransaksiController::class, 'struk'])->name('transaksi.struk');
    });

/*
|--------------------------------------------------------------------------
| Nasabah Self-Service Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:nasabah', 'check.active'])
    ->prefix('nasabah')
    ->name('nasabah.')
    ->group(function () {
        Route::get('/dashboard', [NasabahDashboardController::class, 'index'])->name('dashboard');
        Route::get('/riwayat', [NasabahRiwayatController::class, 'index'])->name('riwayat.index');
        Route::get('/riwayat/{transaksi}', [NasabahRiwayatController::class, 'show'])->name('riwayat.show');
        Route::get('/tarik', [NasabahTarikController::class, 'index'])->name('tarik.index');
        Route::post('/tarik', [NasabahTarikController::class, 'store'])->name('tarik.store');
        Route::get('/profil', [NasabahProfilController::class, 'edit'])->name('profil.edit');
        Route::put('/profil', [NasabahProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/password', [NasabahProfilController::class, 'password'])->name('profil.password');
    });

require __DIR__.'/auth.php';
