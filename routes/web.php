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
    if ($user->isAdmin()) {
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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin', 'check.active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Master Data Unit Bank Sampah
        Route::resource('/units', AdminUnitController::class);
        Route::patch('/units/{unit}/toggle', [AdminUnitController::class, 'toggle'])->name('units.toggle');

        // Master Data Nasabah
        Route::resource('/nasabah', AdminNasabahController::class);
        Route::patch('/nasabah/{nasabah}/toggle-status', [AdminNasabahController::class, 'toggleStatus'])->name('nasabah.toggle-status');

        // Master Data Sampah
        Route::resource('/kategori-sampah', AdminKategoriSampahController::class);
        Route::resource('/jenis-sampah', AdminJenisSampahController::class);
        Route::patch('/jenis-sampah/{jenisSampah}/toggle', [AdminJenisSampahController::class, 'toggle'])->name('jenis-sampah.toggle');
        Route::get('/jenis-sampah/{jenisSampah}/harga-log', [AdminJenisSampahController::class, 'hargaLog'])->name('jenis-sampah.harga-log');

        // Transaksi Warga
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/setor/create', [AdminTransaksiController::class, 'createSetor'])->name('transaksi.setor.create');
        Route::post('/transaksi/setor', [AdminTransaksiController::class, 'storeSetor'])->name('transaksi.setor.store');
        Route::get('/transaksi/tarik/create', [AdminTransaksiController::class, 'createTarik'])->name('transaksi.tarik.create');
        Route::post('/transaksi/tarik', [AdminTransaksiController::class, 'storeTarik'])->name('transaksi.tarik.store');
        Route::get('/transaksi/{transaksi}', [AdminTransaksiController::class, 'show'])->name('transaksi.show');
        Route::get('/transaksi/{transaksi}/struk', [AdminTransaksiController::class, 'struk'])->name('transaksi.struk');
        Route::post('/transaksi/{transaksi}/void', [AdminTransaksiController::class, 'void'])->name('transaksi.void');

        // Persetujuan Penarikan Saldo (Admin / Pengelola Desa)
        Route::get('/persetujuan-tarik', [PersetujuanPenarikanController::class, 'index'])->name('persetujuan-tarik.index');
        Route::post('/persetujuan-tarik/{transaksi}/setujui', [PersetujuanPenarikanController::class, 'setujui'])->name('persetujuan-tarik.setujui');
        Route::post('/persetujuan-tarik/{transaksi}/tolak', [PersetujuanPenarikanController::class, 'tolak'])->name('persetujuan-tarik.tolak');

        // Mitra Pengepul
        Route::resource('/pengepul', AdminPengepulController::class)->except(['show']);
        Route::patch('/pengepul/{pengepul}/toggle', [AdminPengepulController::class, 'toggle'])->name('pengepul.toggle');

        // Penjualan Sampah ke Pengepul & Gudang Desa
        Route::get('/penjualan/stok', [AdminPenjualanSampahController::class, 'stok'])->name('penjualan.stok');
        Route::resource('/penjualan', AdminPenjualanSampahController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/penjualan/{penjualan}/surat-jalan', [AdminPenjualanSampahController::class, 'suratJalan'])->name('penjualan.surat-jalan');

        // Laporan
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/excel', [AdminLaporanController::class, 'exportExcel'])->name('laporan.excel');
        Route::get('/laporan/export/pdf', [AdminLaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/laporan/laba-rugi', [AdminLaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/laba-rugi/pdf', [AdminLaporanController::class, 'exportLabaRugiPdf'])->name('laporan.laba-rugi.pdf');

        // Manajemen Pengguna
        Route::resource('/users', AdminUserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Pengaturan Sistem
        Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [AdminPengaturanController::class, 'update'])->name('pengaturan.update');
    });

/*
|--------------------------------------------------------------------------
| Petugas (Operator) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas,admin', 'check.active'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

        // Nasabah (view-only list for petugas)
        Route::get('/nasabah', [AdminNasabahController::class, 'index'])->name('nasabah.index');
        Route::get('/nasabah/{nasabah}', [AdminNasabahController::class, 'show'])->name('nasabah.show');

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
