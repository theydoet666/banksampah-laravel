# Agent.md — Sistem Informasi Bank Sampah BAA

> File ini adalah panduan untuk AI agent dalam mengembangkan project **Bank Sampah BAA** menggunakan Laravel 11 + MySQL.  
> Baca PRD.md terlebih dahulu sebelum memulai pekerjaan apapun.

---

## Konteks Project

| Info | Detail |
|------|--------|
| Nama Project | Sistem Informasi Bank Sampah BAA |
| Framework | Laravel 11 |
| Database | MySQL 8.x |
| PHP Version | >= 8.2 |
| Node.js | >= 18.x (untuk Vite + TailwindCSS) |
| Package Manager | Composer (PHP) + npm (JS) |
| Local Dev Server | Laragon (Windows) |
| Project Directory | `c:\laragon\www\Bank-Sampah-BAA` |
| Referensi PRD | [PRD.md](./PRD.md) |

---

## Aturan Umum untuk Agent

1. **Selalu baca PRD.md** sebelum membuat fitur baru
2. **Gunakan Bahasa Indonesia** untuk nama variabel yang berhubungan dengan domain bisnis (nasabah, sampah, transaksi) — kecuali nama method Laravel bawaan
3. **Ikuti konvensi Laravel** — gunakan Artisan commands, Eloquent ORM, Form Requests, dll.
4. **Jangan hardcode** — gunakan config, .env, atau app_settings untuk nilai yang bisa berubah
5. **Tulis migration** untuk setiap perubahan schema database
6. **Sertakan seeder** untuk data contoh / master data awal
7. **Validasi selalu di Form Request** — jangan validasi di Controller
8. **Gunakan Service class** untuk business logic yang kompleks (TransaksiService, LaporanService)
9. **Tambahkan Policy** untuk otorisasi resource-level
10. **Semua respons sukses/error** harus menggunakan flash session (`session()->flash()`)

---

## Struktur Peran & Akses

```
1. Admin Desa / Administrator Sistem (`admin`)
 ├── Pengaturan identitas sistem & upload logo
 ├── Manajemen Unit Bank Sampah (Dusun/RW)
 ├── Manajemen Pengguna Sistem (Admin, Pengelola, Petugas Unit)
 ├── Penetapan master kategori & jenis sampah + harga acuan
 └── Akses penuh pemantauan seluruh data & audit log

2. Pengelola Bank Sampah / Bendahara (`pengelola`)
 ├── Otorisasi & Persetujuan Penarikan Saldo Nasabah (Pencairan Kas Desa)
 ├── Monitoring Stok Fisik Sampah Gudang Desa
 ├── Manajemen Mitra Pengepul & Transaksi Penjualan Sampah
 ├── Cetak Surat Jalan Penjualan Sampah ke Pengepul
 ├── Akses Laporan Laba/Rugi Operasional Bank Sampah Desa
 ├── Akses Laporan Transaksi, Rekap Saldo, & Penjualan (PDF/Excel)
 └── Lihat data nasabah & riwayat transaksi seluruh unit (view-only)

3. Petugas Unit (`petugas`)
 ├── Khusus mengelola unit tempat ditugaskan (unit_id)
 ├── Input timbangan setoran sampah warga unit
 ├── Input pengajuan permohonan tarik saldo warga ke desa
 ├── Pendaftaran & pengelolaan nasabah di unitnya
 ├── Riwayat transaksi unitnya
 └── Cetak struk timbangan nasabah

4. Nasabah (`nasabah`)
 ├── Terdaftar pada unit domisilinya
 ├── Akses buku tabungan digital & kartu virtual
 ├── Pengajuan mandiri tarik saldo tabungan via portal HP
 ├── Riwayat mutasi kredit/debit tabungan & cetak bukti
 └── Ubah profil kontak & password akun
```

---

## Stack & Package yang Digunakan

### PHP / Composer
```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "laravel/breeze": "^2.0",
    "barryvdh/laravel-dompdf": "^2.0",
    "maatwebsite/excel": "^3.1",
    "spatie/laravel-permission": "^6.0"
  },
  "require-dev": {
    "fakerphp/faker": "^1.9.1",
    "laravel/pint": "^1.0",
    "pestphp/pest": "^2.0"
  }
}
```

### JS / npm
```json
{
  "devDependencies": {
    "alpinejs": "^3.x",
    "tailwindcss": "^3.x",
    "daisyui": "^4.x",
    "chart.js": "^4.x",
    "vite": "^5.x",
    "@vitejs/plugin-laravel": "latest"
  }
}
```

---

## Konvensi Penamaan

| Komponen | Konvensi | Contoh |
|----------|----------|--------|
| Model | PascalCase, singular | `Unit`, `Nasabah`, `JenisSampah`, `Pengepul`, `PenjualanSampah`, `DetailPenjualanSampah` |
| Controller | PascalCase + Controller | `NasabahController` |
| Migration | snake_case + timestamp | `create_nasabah_table` |
| Table | snake_case, plural | `nasabah`, `jenis_sampah` |
| View | snake_case, direktori per modul | `nasabah/index.blade.php` |
| Route name | dot notation | `nasabah.index`, `transaksi.store` |
| Middleware | PascalCase | `RoleMiddleware` |
| Service | PascalCase + Service | `TransaksiService` |
| Form Request | PascalCase + Request | `StoreNasabahRequest` |

---

## Struktur Route

```php
// routes/web.php
Route::get('/', fn() => redirect()->route('login'));

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/nasabah', NasabahController::class);
    Route::resource('/jenis-sampah', JenisSampahController::class);
    Route::resource('/kategori-sampah', KategoriSampahController::class);
    Route::resource('/transaksi', TransaksiController::class);
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::resource('/users', UserController::class);
});

// Petugas routes
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
    Route::resource('/transaksi', PetugasTransaksiController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/nasabah', [PetugasNasabahController::class, 'index'])->name('nasabah.index');
});

// Nasabah routes
Route::middleware(['auth', 'role:nasabah'])->prefix('nasabah')->name('nasabah.')->group(function () {
    Route::get('/dashboard', [NasabahDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [NasabahRiwayatController::class, 'index'])->name('riwayat');
});
```

---

## Panduan Business Logic Penting

### Auto-generate Nomor Rekening
```php
// Format: BS-{TAHUN}-{5 digit angka}
// Contoh: BS-2026-00001
public static function generateNoRekening(): string
{
    $year = date('Y');
    $lastNasabah = Nasabah::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
    $sequence = $lastNasabah ? (int) substr($lastNasabah->no_rekening, -5) + 1 : 1;
    return sprintf('BS-%s-%05d', $year, $sequence);
}
```

### Auto-generate Kode Transaksi
```php
// Format: TRX-{YYYYMMDD}-{5 digit}
// Contoh: TRX-20261110-00001
public static function generateKodeTransaksi(): string
{
    $date = date('Ymd');
    $lastTrx = Transaksi::whereDate('created_at', today())->orderBy('id', 'desc')->first();
    $sequence = $lastTrx ? (int) substr($lastTrx->kode_transaksi, -5) + 1 : 1;
    return sprintf('TRX-%s-%05d', $date, $sequence);
}
```

### Proses Setoran (TransaksiService)
```php
public function prosesSetoran(array $data): Transaksi
{
    return DB::transaction(function () use ($data) {
        $totalNilai = collect($data['details'])->sum('subtotal');
        $totalBerat = collect($data['details'])->sum('berat');

        $transaksi = Transaksi::create([
            'kode_transaksi' => $this->generateKodeTransaksi(),
            'nasabah_id'     => $data['nasabah_id'],
            'jenis'          => 'setor',
            'total_berat'    => $totalBerat,
            'total_nilai'    => $totalNilai,
            'petugas_id'     => auth()->id(),
            'tanggal_transaksi' => $data['tanggal'],
        ]);

        foreach ($data['details'] as $detail) {
            $transaksi->details()->create($detail);
        }

        // Update saldo nasabah
        $transaksi->nasabah->increment('saldo', $totalNilai);

        return $transaksi;
    });
}
```

### Proses Penarikan (TransaksiService)
```php
public function prosesPenarikan(array $data): Transaksi
{
    return DB::transaction(function () use ($data) {
        $nasabah = Nasabah::findOrFail($data['nasabah_id']);

        if ($nasabah->saldo < $data['nominal']) {
            throw new InsufficientBalanceException('Saldo tidak mencukupi untuk penarikan ini.');
        }

        $transaksi = Transaksi::create([
            'kode_transaksi' => $this->generateKodeTransaksi(),
            'nasabah_id'     => $nasabah->id,
            'jenis'          => 'tarik',
            'total_nilai'    => $data['nominal'],
            'petugas_id'     => auth()->id(),
            'tanggal_transaksi' => $data['tanggal'],
            'catatan'        => $data['catatan'] ?? null,
        ]);

        // Kurangi saldo nasabah
        $nasabah->decrement('saldo', $data['nominal']);

        return $transaksi;
    });
}
```

---

## Urutan Pengerjaan (Recommended Order)

### Sprint 1 — Foundation
1. `composer create-project laravel/laravel .`
2. Setup `.env` (DB connection ke MySQL Laragon)
3. Install Breeze: `composer require laravel/breeze && php artisan breeze:install blade`
4. Install TailwindCSS + DaisyUI
5. Buat semua Migration (urutan sesuai foreign key dependency)
6. Buat Seeder: UserSeeder, KategoriSampahSeeder, JenisSampahSeeder
7. Buat `RoleMiddleware` dan daftarkan di `bootstrap/app.php`
8. Buat Model + Relationship
9. Buat Dashboard (Admin, Petugas, Nasabah) — static dulu

### Sprint 2 — Master Data
10. CRUD Nasabah (NasabahController + Views)
11. CRUD Kategori Sampah
12. CRUD Jenis Sampah (dengan log harga)
13. CRUD User (admin only)

### Sprint 3 — Transaksi
14. Form Setoran (multi-item, real-time kalkulasi dengan Alpine.js)
15. Form Penarikan (dengan validasi saldo)
16. Riwayat Transaksi (filter + paginate)
17. Detail Transaksi
18. Void Transaksi (admin only)
19. Cetak Struk (print-friendly view)

### Sprint 4 — Laporan & Dashboard
20. Dashboard stats (Chart.js)
21. Laporan Transaksi (filter)
22. Ekspor PDF (DomPDF)
23. Ekspor Excel (Maatwebsite)
24. Panel Nasabah self-service

### Sprint 5 — Polish
25. Pengaturan sistem (app_settings)
26. Edit profil & ganti password
27. Validasi form lengkap
28. Flash messages & konfirmasi modal
29. Mobile responsiveness check
30. Seed data demo

---

## Perintah Artisan Penting

```bash
# Setup awal
php artisan migrate:fresh --seed

# Buat komponen baru
php artisan make:model Nasabah -mscr     # Model + Migration + Seeder + Controller + Resource
php artisan make:request StoreNasabahRequest
php artisan make:policy NasabahPolicy --model=Nasabah
php artisan make:middleware RoleMiddleware

# Utility
php artisan route:list --name=nasabah   # Cek routes
php artisan tinker                       # REPL untuk test query
php artisan optimize:clear               # Clear semua cache
php artisan bank-sampah:reset-data       # Reset seluruh data transaksi & nasabah dummy untuk testing bersih

---

## Checklist Keamanan

- [ ] Semua route dilindungi middleware `auth`
- [ ] Role check di semua controller dengan middleware atau Policy
- [ ] Form Request digunakan untuk semua input
- [ ] CSRF token ada di semua form (`@csrf`)
- [ ] File upload: validasi `mimes:jpeg,png,jpg` dan `max:2048`
- [ ] Soft delete untuk nasabah (jangan hard delete)
- [ ] Log perubahan harga sampah di `harga_sampah_log`
- [ ] Void transaksi tidak menghapus record — hanya ubah status

---

## Catatan Pengembangan

> **PENTING**: Saldo nasabah di tabel `nasabah.saldo` adalah denormalized cache.  
> Selalu update melalui `increment()` / `decrement()` di dalam DB transaction.  
> Jangan hitung ulang dari transaksi di setiap request untuk performa.

> **PENTING**: Struktur harga komoditas terbagi dua di tabel `jenis_sampah`:  
> - `harga_beli`: Harga tebus saat warga menyetor (menambah saldo nasabah).  
> - `harga_jual`: Harga standar saat dijual ke pabrik/pengepul (memberi margin keuntungan kas desa).  
> Satuan ukur mendukung: `kg`, `gram`, `unit`, `buah`, `pcs`, `liter`.

> **PENTING**: Harga sampah di `detail_transaksi.harga_satuan` adalah snapshot harga  
> saat transaksi terjadi — BUKAN foreign key ke harga yang bisa berubah.  
> Ini memastikan laporan historis tetap akurat.

> **INFO**: Gunakan `DB::transaction()` untuk semua operasi yang mengubah saldo  
> dan menyimpan transaksi secara bersamaan (atomic operation).

> **LOGIN NASABAH & SHORTHAND**: `LoginRequest` mendukung login fleksibel via Email,  
> Nomor Rekening (`BS-YYYY-xxxxx`), Nomor HP/WhatsApp, dan Shorthand Role (`admin`, `pengelola`, `petugas`).  
> Password default nasabah yang didaftarkan di loket pos adalah 6 digit terakhir No. HP.

