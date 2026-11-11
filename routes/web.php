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

// Petugas Controllers
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;

// Nasabah Controllers
use App\Http\Controllers\Nasabah\DashboardController as NasabahDashboardController;
use App\Http\Controllers\Nasabah\RiwayatController as NasabahRiwayatController;
use App\Http\Controllers\Nasabah\ProfilController as NasabahProfilController;

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

        // Master Data Nasabah
        Route::resource('/nasabah', AdminNasabahController::class);
        Route::patch('/nasabah/{nasabah}/toggle-status', [AdminNasabahController::class, 'toggleStatus'])->name('nasabah.toggle-status');

        // Master Data Sampah
        Route::resource('/kategori-sampah', AdminKategoriSampahController::class);
        Route::resource('/jenis-sampah', AdminJenisSampahController::class);
        Route::patch('/jenis-sampah/{jenisSampah}/toggle', [AdminJenisSampahController::class, 'toggle'])->name('jenis-sampah.toggle');
        Route::get('/jenis-sampah/{jenisSampah}/harga-log', [AdminJenisSampahController::class, 'hargaLog'])->name('jenis-sampah.harga-log');

        // Transaksi
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/setor/create', [AdminTransaksiController::class, 'createSetor'])->name('transaksi.setor.create');
        Route::post('/transaksi/setor', [AdminTransaksiController::class, 'storeSetor'])->name('transaksi.setor.store');
        Route::get('/transaksi/tarik/create', [AdminTransaksiController::class, 'createTarik'])->name('transaksi.tarik.create');
        Route::post('/transaksi/tarik', [AdminTransaksiController::class, 'storeTarik'])->name('transaksi.tarik.store');
        Route::get('/transaksi/{transaksi}', [AdminTransaksiController::class, 'show'])->name('transaksi.show');
        Route::get('/transaksi/{transaksi}/struk', [AdminTransaksiController::class, 'struk'])->name('transaksi.struk');
        Route::post('/transaksi/{transaksi}/void', [AdminTransaksiController::class, 'void'])->name('transaksi.void');

        // Laporan
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/excel', [AdminLaporanController::class, 'exportExcel'])->name('laporan.excel');
        Route::get('/laporan/export/pdf', [AdminLaporanController::class, 'exportPdf'])->name('laporan.pdf');

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
        Route::get('/profil', [NasabahProfilController::class, 'edit'])->name('profil.edit');
        Route::put('/profil', [NasabahProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/password', [NasabahProfilController::class, 'password'])->name('profil.password');
    });

require __DIR__.'/auth.php';
