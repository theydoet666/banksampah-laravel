# Prompt.md — Panduan Prompt Pengembangan Bertahap
# Sistem Informasi Bank Sampah BAA

> **Referensi Wajib:**
> - [`PRD.md`](./PRD.md) — Product Requirements Document (fitur, database, alur bisnis)
> - [`Agent.md`](./Agent.md) — Panduan teknis, konvensi kode, business logic
>
> **Cara Penggunaan:**
> Jalankan setiap prompt secara berurutan ke AI Agent (Antigravity/Claude).
> Setiap prompt adalah satu sesi kerja yang mandiri namun saling berkaitan.
> Jangan loncat langkah — setiap tahap membangun fondasi untuk tahap berikutnya.

---

## FASE 0 — PERSIAPAN & INSTALASI

---

### PROMPT 0.1 — Instalasi Laravel 11

```
Baca Agent.md dan PRD.md terlebih dahulu.

Saya ingin membangun project Sistem Informasi Bank Sampah BAA menggunakan Laravel 11.
Project sudah ada di direktori: c:\laragon\www\Bank-Sampah-BAA
Direktori sudah berisi file PRD.md dan Agent.md saja.

Lakukan langkah berikut:
1. Install Laravel 11 fresh di direktori tersebut menggunakan composer
   (direktori tidak kosong, gunakan flag yang sesuai)
2. Konfigurasikan file .env untuk:
   - APP_NAME=Bank Sampah BAA
   - APP_URL=http://bank-sampah-baa.test
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=bank_sampah_baa
   - DB_USERNAME=root
   - DB_PASSWORD=(kosong, default Laragon)
3. Generate APP_KEY
4. Verifikasi instalasi berhasil dengan php artisan --version

Pastikan file PRD.md dan Agent.md tidak terhapus.
```

---

### PROMPT 0.2 — Install Package Composer

```
Baca Agent.md bagian "Stack & Package yang Digunakan".

Install semua package PHP yang diperlukan untuk project Bank Sampah BAA:

Package Production:
- laravel/breeze ^2.0 (autentikasi)
- barryvdh/laravel-dompdf ^2.0 (export PDF)
- maatwebsite/excel ^3.1 (export Excel)

Setelah install semua package via composer, jalankan:
php artisan breeze:install blade

Jangan install TailwindCSS lewat npm dulu — itu langkah berikutnya.
Verifikasi semua package berhasil terinstall di composer.json.
```

---

### PROMPT 0.3 — Setup Frontend (TailwindCSS + DaisyUI + Alpine.js + Chart.js)

```
Baca Agent.md bagian "JS / npm".

Setup frontend untuk project Bank Sampah BAA:

1. Install npm packages:
   - tailwindcss ^3.x
   - daisyui ^4.x  
   - alpinejs ^3.x
   - chart.js ^4.x
   - @tailwindcss/forms

2. Konfigurasi tailwind.config.js:
   - content path mencakup semua file blade di resources/views/**
   - theme extend: tambahkan warna brand "hijau" sebagai primary color
     primary: '#16a34a' (green-600)
   - plugins: daisyui
   - daisyui themes: hanya gunakan theme "light" dan custom theme "banksampah"

3. Update resources/css/app.css:
   - Import Tailwind directives (@tailwind base, components, utilities)

4. Update resources/js/app.js:
   - Import Alpine.js dan Chart.js

5. Update vite.config.js agar include TailwindCSS

6. Jalankan npm run build untuk verifikasi

Referensi PRD.md bagian "9. UI/UX Guidelines" untuk panduan tampilan.
```

---

## FASE 1 — DATABASE & MODEL

---

### PROMPT 1.1 — Buat Semua Migration

```
Baca PRD.md bagian "5. Desain Database" dengan teliti.

Buat migration untuk semua tabel berikut SESUAI URUTAN (karena ada foreign key dependency):

URUTAN PEMBUATAN:
1. Modifikasi tabel users yang sudah ada dari Breeze:
   - Tambahkan kolom: role ENUM('admin','petugas','nasabah') DEFAULT 'admin'
   - Tambahkan kolom: is_active BOOLEAN DEFAULT 1
   (buat migration alter table, JANGAN edit migration bawaan Breeze)

2. Buat tabel kategori_sampah:
   - id, nama VARCHAR(100), deskripsi TEXT nullable
   - timestamps

3. Buat tabel jenis_sampah:
   - id, kategori_id FK→kategori_sampah
   - nama VARCHAR(100)
   - satuan ENUM('kg','gram') DEFAULT 'kg'
   - harga_per_satuan DECIMAL(10,2)
   - deskripsi TEXT nullable
   - is_active BOOLEAN DEFAULT 1
   - timestamps

4. Buat tabel harga_sampah_log:
   - id, jenis_sampah_id FK→jenis_sampah
   - harga_lama DECIMAL(10,2), harga_baru DECIMAL(10,2)
   - changed_by FK→users
   - created_at TIMESTAMP (tidak perlu updated_at)

5. Buat tabel nasabah:
   - id, user_id FK→users NULLABLE
   - no_rekening VARCHAR(20) UNIQUE
   - nama VARCHAR(100), nik VARCHAR(16) UNIQUE nullable
   - tempat_lahir VARCHAR(100) nullable, tanggal_lahir DATE nullable
   - jenis_kelamin ENUM('L','P')
   - alamat TEXT, no_hp VARCHAR(20)
   - foto VARCHAR(255) nullable
   - saldo DECIMAL(15,2) DEFAULT 0
   - status ENUM('aktif','nonaktif') DEFAULT 'aktif'
   - registered_by FK→users
   - timestamps + softDeletes

6. Buat tabel transaksi:
   - id, kode_transaksi VARCHAR(30) UNIQUE
   - nasabah_id FK→nasabah
   - jenis ENUM('setor','tarik')
   - total_berat DECIMAL(10,3) nullable
   - total_nilai DECIMAL(15,2)
   - catatan TEXT nullable
   - status ENUM('aktif','void') DEFAULT 'aktif'
   - petugas_id FK→users
   - tanggal_transaksi DATE
   - timestamps

7. Buat tabel detail_transaksi:
   - id, transaksi_id FK→transaksi
   - jenis_sampah_id FK→jenis_sampah
   - berat DECIMAL(10,3)
   - harga_satuan DECIMAL(10,2)
   - subtotal DECIMAL(15,2)
   - timestamps

8. Buat tabel app_settings:
   - id, key VARCHAR(100) UNIQUE, value TEXT
   - timestamps

Setelah semua migration dibuat, jalankan: php artisan migrate
Pastikan tidak ada error.
```

---

### PROMPT 1.2 — Buat Semua Model & Relationship

```
Baca PRD.md bagian "5. Desain Database" dan Agent.md bagian "Konvensi Penamaan".

Buat/update Model Eloquent untuk semua entitas berikut LENGKAP dengan relationship:

1. app/Models/User.php (UPDATE yang sudah ada)
   - Tambah $fillable: name, email, password, role, is_active
   - Tambah cast: is_active → boolean
   - Relationship:
     * hasMany(Nasabah::class, 'registered_by')
     * hasMany(Transaksi::class, 'petugas_id')
     * hasMany(HargaSampahLog::class, 'changed_by')
   - Helper method: isAdmin(), isPetugas(), isNasabah() — return bool
   - Scope: scopeActive($query)

2. app/Models/Nasabah.php (BUAT BARU)
   - $fillable semua kolom
   - $casts: saldo → decimal:2, tanggal_lahir → date, status
   - use SoftDeletes
   - Relationship:
     * belongsTo(User::class, 'user_id')
     * belongsTo(User::class, 'registered_by')
     * hasMany(Transaksi::class)
   - Static method: generateNoRekening() — lihat Agent.md untuk logikanya
   - Scope: scopeAktif($query)
   - Accessor: getFormattedSaldoAttribute() — format Rupiah

3. app/Models/KategoriSampah.php (BUAT BARU)
   - $fillable: nama, deskripsi
   - Relationship: hasMany(JenisSampah::class, 'kategori_id')

4. app/Models/JenisSampah.php (BUAT BARU)
   - $fillable semua kolom
   - $casts: is_active → boolean, harga_per_satuan → decimal:2
   - Relationship:
     * belongsTo(KategoriSampah::class, 'kategori_id')
     * hasMany(HargaSampahLog::class)
     * hasMany(DetailTransaksi::class)
   - Scope: scopeActive($query)

5. app/Models/HargaSampahLog.php (BUAT BARU)
   - $fillable: jenis_sampah_id, harga_lama, harga_baru, changed_by
   - const UPDATED_AT = null (tidak ada updated_at)
   - Relationship:
     * belongsTo(JenisSampah::class)
     * belongsTo(User::class, 'changed_by')

6. app/Models/Transaksi.php (BUAT BARU)
   - $fillable semua kolom
   - $casts: total_nilai → decimal:2, total_berat → decimal:3, tanggal_transaksi → date
   - Relationship:
     * belongsTo(Nasabah::class)
     * belongsTo(User::class, 'petugas_id')
     * hasMany(DetailTransaksi::class)
   - Static method: generateKodeTransaksi() — lihat Agent.md untuk logikanya
   - Scope: scopeSetor(), scopeTarik(), scopeAktif()

7. app/Models/DetailTransaksi.php (BUAT BARU)
   - $fillable: transaksi_id, jenis_sampah_id, berat, harga_satuan, subtotal
   - $casts: semua decimal dengan presisi sesuai
   - Relationship:
     * belongsTo(Transaksi::class)
     * belongsTo(JenisSampah::class)

8. app/Models/AppSetting.php (BUAT BARU)
   - $fillable: key, value
   - Static method: get(string $key, $default = null)
   - Static method: set(string $key, $value)

Verifikasi semua relationship dengan php artisan tinker setelah selesai.
```

---

### PROMPT 1.3 — Buat Seeder & Data Awal

```
Baca PRD.md bagian "2. Stakeholders" dan Agent.md bagian "Urutan Pengerjaan".

Buat Seeder berikut:

1. database/seeders/UserSeeder.php
   Data yang harus dibuat:
   - Admin: name="Administrator", email="admin@banksampah.test", password="password", role="admin"
   - Petugas: name="Petugas Satu", email="petugas@banksampah.test", password="password", role="petugas"
   - Nasabah demo: name="Budi Santoso", email="nasabah@banksampah.test", password="password", role="nasabah"

2. database/seeders/KategoriSampahSeeder.php
   Kategori yang harus dibuat:
   - Plastik (berbagai jenis plastik daur ulang)
   - Kertas (kertas, kardus, koran)
   - Logam (besi, aluminium, tembaga)
   - Kaca (botol kaca, pecahan kaca)
   - Elektronik (barang elektronik bekas)
   - Organik (sisa makanan, daun)

3. database/seeders/JenisSampahSeeder.php
   Jenis sampah per kategori (minimal 2-3 per kategori):
   Plastik:
   - Botol PET (aqua, minuman) → Rp 3.000/kg
   - Plastik HD (kantong, ember) → Rp 2.000/kg
   - Plastik PP (sedotan, tali) → Rp 1.500/kg
   Kertas:
   - Kardus/Dupleks → Rp 2.500/kg
   - Koran/Majalah → Rp 1.500/kg
   - HVS/Kertas Putih → Rp 2.000/kg
   Logam:
   - Besi Tua → Rp 5.000/kg
   - Aluminium → Rp 15.000/kg
   - Tembaga → Rp 50.000/kg
   Kaca:
   - Botol Kaca → Rp 1.000/kg
   Elektronik:
   - Barang Elektronik → Rp 3.000/kg
   Organik:
   - Sampah Organik → Rp 500/kg

4. database/seeders/NasabahSeeder.php
   Buat 5 nasabah demo (gunakan Faker untuk data, registered_by = petugas user id)
   Satu nasabah wajib dihubungkan ke user nasabah yang dibuat di UserSeeder

5. database/seeders/AppSettingSeeder.php
   Data settings:
   - nama_bank_sampah → "Bank Sampah BAA"
   - alamat → "Jl. Lingkungan Hidup No. 1, Kota Bersih"
   - no_telp → "08123456789"
   - email → "info@banksampah.test"
   - logo → null
   - tahun_berdiri → "2020"

6. Update DatabaseSeeder.php untuk memanggil semua seeder di atas secara berurutan

Jalankan: php artisan migrate:fresh --seed
Verifikasi data berhasil masuk ke database.
```

---

## FASE 2 — AUTENTIKASI & MIDDLEWARE

---

### PROMPT 2.1 — Role Middleware & Auth Redirect

```
Baca Agent.md bagian "Struktur Peran & Akses" dan "Struktur Route".

Buat sistem autentikasi berbasis role:

1. Buat app/Http/Middleware/RoleMiddleware.php
   - Terima parameter role yang bisa multiple, contoh: role:admin atau role:admin,petugas
   - Jika user tidak punya role yang dimaksud → abort(403)
   - Jika user tidak login → redirect ke login

2. Daftarkan middleware di bootstrap/app.php:
   withMiddleware(function (Middleware $middleware) {
       $middleware->alias(['role' => RoleMiddleware::class]);
   })

3. Update app/Http/Controllers/Auth/AuthenticatedSessionController.php
   Override method store() agar setelah login redirect ke halaman sesuai role:
   - admin → route('admin.dashboard')
   - petugas → route('petugas.dashboard')
   - nasabah → route('nasabah.dashboard')

4. Buat app/Http/Middleware/CheckActive.php
   - Cek apakah user (is_active = 1)
   - Jika tidak aktif → logout + redirect login dengan pesan "Akun Anda dinonaktifkan"

5. Update routes/web.php sesuai struktur di Agent.md:
   - Route root '/' redirect ke login
   - Group route admin dengan prefix 'admin', middleware ['auth', 'role:admin', 'check.active']
   - Group route petugas dengan prefix 'petugas', middleware ['auth', 'role:petugas,admin', 'check.active']
   - Group route nasabah dengan prefix 'nasabah', middleware ['auth', 'role:nasabah', 'check.active']
   - Semua route controller masih bisa pakai placeholder untuk sekarang (return view dummy)

6. Kustomisasi halaman login Breeze:
   - Ubah tampilan agar sesuai branding Bank Sampah BAA
   - Gunakan TailwindCSS + DaisyUI
   - Tampilkan nama/logo bank sampah dari AppSetting
   - Background warna hijau (green-600)
   - Form login di tengah halaman dengan card putih
   - Tambahkan credit footer: "© Bank Sampah BAA"

Test login dengan ketiga akun (admin, petugas, nasabah) dan pastikan redirect benar.
```

---

## FASE 3 — LAYOUT & KOMPONEN UI

---

### PROMPT 3.1 — Buat Layout Utama (Admin & Petugas)

```
Baca PRD.md bagian "9. UI/UX Guidelines".

Buat layout utama untuk panel admin dan petugas:

1. Buat resources/views/layouts/app.blade.php (layout utama)
   Struktur:
   - <head>: meta, title (dinamis dengan @yield('title')), Vite CSS/JS
   - <body>: 
     * Sidebar fixed di kiri (w-64)
     * Main content area di kanan (ml-64)
     * Topbar/navbar di atas

2. Buat resources/views/layouts/partials/sidebar.blade.php
   Sidebar berisi:
   - Logo + Nama Bank Sampah (dari AppSetting atau config)
   - Info user yang login (nama + badge role)
   - Menu navigasi dengan icon (gunakan Heroicons SVG inline):
     
     Untuk ADMIN:
     * Dashboard (icon: home)
     * Data Master:
       - Nasabah (icon: users)
       - Kategori Sampah (icon: tag)
       - Jenis Sampah (icon: cube)
       - Pengguna Sistem (icon: user-group)
     * Transaksi:
       - Setor Sampah (icon: arrow-down-tray)
       - Tarik Saldo (icon: arrow-up-tray)
       - Riwayat Transaksi (icon: clock)
     * Laporan:
       - Laporan Transaksi (icon: document-text)
       - Rekap Saldo (icon: banknotes)
     * Pengaturan (icon: cog)

     Untuk PETUGAS:
     * Dashboard
     * Nasabah (hanya lihat)
     * Setor Sampah
     * Tarik Saldo
     * Riwayat Transaksi

   - Menu aktif di-highlight dengan warna hijau
   - Gunakan @auth dan @can atau cek role untuk tampilkan menu yang sesuai

3. Buat resources/views/layouts/partials/topbar.blade.php
   - Tombol toggle sidebar (untuk mobile)
   - Breadcrumb navigasi
   - Dropdown user: nama user, link ke profil, tombol logout

4. Buat resources/views/layouts/partials/flash-message.blade.php
   - Tampilkan session flash: success (hijau), error (merah), warning (kuning), info (biru)
   - Auto-dismiss setelah 5 detik menggunakan Alpine.js

5. Buat resources/views/layouts/nasabah.blade.php (layout khusus panel nasabah)
   - Lebih sederhana dari layout admin
   - Topbar dengan nama user + saldo
   - Menu minimal: Dashboard, Riwayat, Profil, Logout

6. Buat resources/views/components/card.blade.php (Blade component)
   - Reusable card dengan slot title dan slot default
   - Props: title, class

7. Buat resources/views/components/stat-card.blade.php
   - Card untuk statistik dashboard
   - Props: title, value, icon, color (green/blue/yellow/red)

Pastikan layout responsive dan berfungsi di mobile dengan hamburger menu.
```

---

## FASE 4 — DASHBOARD

---

### PROMPT 4.1 — Dashboard Admin

```
Baca PRD.md bagian "4.2 Dashboard" (DASH-01 sampai DASH-06).
Baca Agent.md bagian "Urutan Pengerjaan Sprint 4".

Buat Dashboard Admin yang lengkap:

1. Buat app/Http/Controllers/Admin/DashboardController.php
   Method index() harus mengirimkan data:
   - $totalNasabah → count nasabah dengan status 'aktif'
   - $totalSampahBulanIni → SUM detail_transaksi.berat untuk transaksi 'setor' bulan ini
   - $totalSetoranBulanIni → SUM transaksi.total_nilai untuk jenis 'setor' bulan ini
   - $totalPenarikanBulanIni → SUM transaksi.total_nilai untuk jenis 'tarik' bulan ini
   - $totalJenisSampah → count jenis_sampah aktif
   - $transaksiTerbaru → 5 transaksi terbaru dengan relasi nasabah + petugas
   - $chartData → array data transaksi 7 hari terakhir:
     * labels: array nama hari ['Sen', 'Sel', ...]
     * setor: array total nilai setor per hari
     * tarik: array total nilai tarik per hari

2. Buat resources/views/admin/dashboard.blade.php
   Layout:
   - Row 1: 4 stat card (Total Nasabah, Sampah Masuk, Total Setoran, Total Penarikan)
   - Row 2: Grafik Line Chart (Chart.js) + Card 5 Transaksi Terbaru
   - Row 3: Jumlah Jenis Sampah Aktif (badge/chip per kategori)
   
   Stat Card detail:
   - Total Nasabah Aktif (icon orang, warna biru)
   - Total Sampah Masuk bulan ini dalam kg (icon timbangan, warna hijau)
   - Total Setoran bulan ini dalam Rupiah (icon uang masuk, warna hijau)
   - Total Penarikan bulan ini dalam Rupiah (icon uang keluar, warna merah)

3. Implementasi Chart.js:
   - Line chart dengan 2 dataset: Setoran dan Penarikan
   - X-axis: 7 hari terakhir
   - Y-axis: nilai dalam Rupiah
   - Gunakan @json() untuk pass data dari controller ke JS

4. Daftarkan route: GET /admin/dashboard → admin.dashboard

5. Format angka Rupiah menggunakan Blade helper atau JavaScript:
   Buat file app/Helpers/helpers.php:
   - function formatRupiah($angka): string — format ke "Rp 1.500.000"
   - Daftarkan di composer.json autoload.files

Verifikasi dashboard muncul dengan data yang benar setelah login sebagai admin.
```

---

### PROMPT 4.2 — Dashboard Petugas & Nasabah

```
Buat dashboard untuk role Petugas dan Nasabah.

1. Dashboard Petugas (app/Http/Controllers/Petugas/DashboardController.php)
   Data yang ditampilkan:
   - Jumlah transaksi yang dibuat petugas ini hari ini
   - Jumlah transaksi yang dibuat petugas ini bulan ini
   - Total nilai transaksi petugas ini bulan ini
   - 5 transaksi terbaru oleh petugas yang login
   - Shortcut button: Setor Sampah, Tarik Saldo, Daftar Nasabah Baru
   
   View: resources/views/petugas/dashboard.blade.php

2. Dashboard Nasabah (app/Http/Controllers/Nasabah/DashboardController.php)
   Data yang ditampilkan:
   - Saldo tabungan saat ini (tampilkan besar, mencolok)
   - Total setoran bulan ini
   - Total penarikan bulan ini
   - 5 riwayat transaksi terakhir nasabah yang login
   - Nomor rekening nasabah
   
   View: resources/views/nasabah/dashboard.blade.php
   Gunakan layout nasabah (resources/views/layouts/nasabah.blade.php)
```

---

## FASE 5 — MANAJEMEN NASABAH

---

### PROMPT 5.1 — CRUD Nasabah (Backend)

```
Baca PRD.md bagian "4.3 Manajemen Nasabah" (NAS-01 sampai NAS-05).
Baca Agent.md bagian "Business Logic: generateNoRekening()".

Buat sistem manajemen nasabah lengkap:

1. Buat app/Http/Requests/StoreNasabahRequest.php
   Rules:
   - nama: required, string, max:100
   - nik: nullable, string, digits:16, unique:nasabah,nik
   - jenis_kelamin: required, in:L,P
   - alamat: required, string
   - no_hp: required, string, max:20
   - tempat_lahir: nullable, string, max:100
   - tanggal_lahir: nullable, date, before:today
   - foto: nullable, image, mimes:jpeg,png,jpg, max:2048
   - status: required, in:aktif,nonaktif

2. Buat app/Http/Requests/UpdateNasabahRequest.php
   - Sama seperti Store tapi nik unique ignore current nasabah

3. Buat app/Services/NasabahService.php
   Methods:
   - store(array $data): Nasabah
     * Generate no_rekening otomatis (format BS-YYYY-XXXXX)
     * Handle upload foto ke storage/app/public/nasabah/
     * Set registered_by = auth()->id()
     * Return nasabah yang dibuat
   - update(Nasabah $nasabah, array $data): Nasabah
     * Handle ganti foto (hapus foto lama jika ada)
     * Update data nasabah
   - toggleStatus(Nasabah $nasabah): Nasabah
     * Toggle antara 'aktif' dan 'nonaktif'

4. Buat app/Http/Controllers/Admin/NasabahController.php
   Methods:
   - index(): tampil daftar + search by nama/no_rekening/no_hp, paginate 15
   - create(): tampil form tambah
   - store(StoreNasabahRequest $request): simpan + redirect dengan flash success
   - show(Nasabah $nasabah): detail nasabah + 10 transaksi terakhir
   - edit(Nasabah $nasabah): tampil form edit
   - update(UpdateNasabahRequest $request, Nasabah $nasabah): update + redirect
   - toggleStatus(Nasabah $nasabah): aktif/nonaktif + redirect (admin only)

5. Daftarkan routes di routes/web.php dalam group admin:
   Route::resource('/nasabah', NasabahController::class);
   Route::patch('/nasabah/{nasabah}/toggle-status', [NasabahController::class, 'toggleStatus'])
        ->name('nasabah.toggle-status');

6. Jalankan: php artisan storage:link (agar foto bisa diakses dari public)
```

---

### PROMPT 5.2 — CRUD Nasabah (Frontend Views)

```
Baca PRD.md bagian "9. UI/UX Guidelines".

Buat semua view untuk manajemen nasabah:

1. resources/views/admin/nasabah/index.blade.php
   - Tabel daftar nasabah dengan kolom:
     No. Rekening | Nama | No. HP | Saldo | Status | Aksi
   - Search bar di atas tabel (GET parameter ?search=)
   - Badge status: hijau untuk 'aktif', abu untuk 'nonaktif'
   - Saldo diformat Rupiah
   - Aksi: tombol Detail (biru), Edit (kuning), Toggle Status (merah/hijau)
   - Tombol "Tambah Nasabah" di kanan atas
   - Paginasi di bawah tabel
   - Empty state jika tidak ada data

2. resources/views/admin/nasabah/create.blade.php
   Form tambah nasabah:
   - Field: Nama, NIK, Jenis Kelamin (radio), Tempat Lahir, Tanggal Lahir
   - Field: Alamat (textarea), No. HP, Status, Foto (input file + preview)
   - Preview foto menggunakan Alpine.js (tampilkan sebelum upload)
   - Validation error ditampilkan di bawah setiap field
   - Tombol Simpan + Batal (back)

3. resources/views/admin/nasabah/edit.blade.php
   - Sama seperti create, tapi field terisi dengan data existing
   - Tampilkan foto saat ini + opsi ganti foto

4. resources/views/admin/nasabah/show.blade.php
   - Card info profil nasabah (foto, nama, NIK, alamat, no. HP, no. rekening)
   - Card saldo (tampil besar)
   - Tabel riwayat 10 transaksi terakhir (kode, jenis, nilai, tanggal)
   - Tombol kembali ke daftar, tombol edit nasabah
   - Tombol "Setor Sampah" dan "Tarik Saldo" (shortcut)

5. Modal konfirmasi toggle status:
   - Gunakan Alpine.js untuk show/hide modal
   - Pesan: "Apakah Anda yakin ingin [nonaktifkan/aktifkan] nasabah ini?"
   - Tombol Konfirmasi (submit form) + Batal

Pastikan semua form menggunakan @csrf dan method spoofing yang benar (@method('PUT')).
```

---

## FASE 6 — MANAJEMEN SAMPAH

---

### PROMPT 6.1 — CRUD Kategori Sampah

```
Baca PRD.md bagian "4.4 Manajemen Jenis Sampah" (SAM-06).

Buat CRUD Kategori Sampah (simple):

1. Buat app/Http/Requests/KategoriSampahRequest.php
   - nama: required, string, max:100, unique:kategori_sampah,nama (dengan ignore untuk update)
   - deskripsi: nullable, string

2. Buat app/Http/Controllers/Admin/KategoriSampahController.php
   - index(): daftar kategori + count jenis sampah per kategori
   - create(), store(), edit(), update(), destroy()
   - destroy(): cek jika masih ada jenis_sampah aktif → tolak hapus dengan pesan error
   
3. Buat views:
   resources/views/admin/kategori-sampah/index.blade.php
   - Tabel: Nama Kategori | Jumlah Jenis | Deskripsi | Aksi
   - Tombol Edit + Hapus (dengan modal konfirmasi)
   - Tombol "Tambah Kategori" di kanan atas
   
   resources/views/admin/kategori-sampah/create.blade.php
   resources/views/admin/kategori-sampah/edit.blade.php
   - Form sederhana: Nama Kategori + Deskripsi

4. Daftarkan route: Route::resource('/kategori-sampah', KategoriSampahController::class)
```

---

### PROMPT 6.2 — CRUD Jenis Sampah (Dengan Log Harga)

```
Baca PRD.md bagian "4.4 Manajemen Jenis Sampah" (SAM-01 sampai SAM-05).
Baca Agent.md bagian "Catatan Pengembangan" mengenai harga snapshot.

Buat CRUD Jenis Sampah lengkap dengan tracking perubahan harga:

1. Buat app/Http/Requests/JenisSampahRequest.php
   - kategori_id: required, exists:kategori_sampah,id
   - nama: required, string, max:100
   - satuan: required, in:kg,gram
   - harga_per_satuan: required, numeric, min:0
   - deskripsi: nullable, string
   - is_active: boolean

2. Buat app/Http/Controllers/Admin/JenisSampahController.php
   - index(): daftar dengan eager load kategori, filter by kategori (GET ?kategori_id=)
   - create(): form + list kategori untuk dropdown
   - store(): simpan + catat ke harga_sampah_log sebagai harga awal (harga_lama=0)
   - edit(): form edit dengan harga saat ini
   - update(): 
     * Jika harga berubah → simpan ke harga_sampah_log dulu
     * Update jenis_sampah
   - toggleActive(): aktif/nonaktif

3. Buat views:
   resources/views/admin/jenis-sampah/index.blade.php
   - Tabel: Nama | Kategori | Satuan | Harga/Satuan | Status | Aksi
   - Filter dropdown kategori di atas tabel
   - Badge status aktif/nonaktif
   - Harga diformat Rupiah
   - Tombol Edit + Toggle Status + Riwayat Harga

   resources/views/admin/jenis-sampah/create.blade.php
   resources/views/admin/jenis-sampah/edit.blade.php
   - Dropdown kategori
   - Input harga (format angka, tampilkan Rupiah)
   
   resources/views/admin/jenis-sampah/harga-log.blade.php
   - Tabel riwayat perubahan harga (dari harga_sampah_log)
   - Kolom: Tanggal | Harga Lama | Harga Baru | Diubah Oleh
   - Tampilkan perubahan naik/turun dengan warna/icon

4. Route tambahan:
   Route::get('/jenis-sampah/{jenisSampah}/harga-log', ...)->name('jenis-sampah.harga-log')
   Route::patch('/jenis-sampah/{jenisSampah}/toggle', ...)->name('jenis-sampah.toggle')
```

---

## FASE 7 — TRANSAKSI

---

### PROMPT 7.1 — Service Layer Transaksi

```
Baca Agent.md bagian "Panduan Business Logic Penting" dengan sangat teliti.
Baca PRD.md bagian "7. Alur Proses Bisnis".

Buat Service class untuk semua logika bisnis transaksi:

1. Buat app/Exceptions/InsufficientBalanceException.php
   - Extend Exception
   - Default message: "Saldo nasabah tidak mencukupi untuk penarikan ini."

2. Buat app/Services/TransaksiService.php dengan methods:

   a) generateKodeTransaksi(): string
      - Format: TRX-{YYYYMMDD}-{5 digit sequence}
      - Sequence reset setiap hari baru
      - Lihat Agent.md untuk detail implementasi

   b) prosesSetoran(array $data): Transaksi
      - Gunakan DB::transaction()
      - Hitung total_nilai dan total_berat dari $data['details']
      - Buat record Transaksi (jenis='setor')
      - Buat record DetailTransaksi untuk setiap item sampah
        * Ambil harga_per_satuan dari JenisSampah saat ini (snapshot!)
        * Hitung subtotal = berat × harga_per_satuan
      - Increment saldo nasabah
      - Return Transaksi

   c) prosesPenarikan(array $data): Transaksi
      - Gunakan DB::transaction()
      - Cek saldo nasabah mencukupi → throw InsufficientBalanceException jika tidak
      - Buat record Transaksi (jenis='tarik')
      - Decrement saldo nasabah
      - Return Transaksi

   d) voidTransaksi(Transaksi $transaksi): void
      - Hanya admin yang bisa (cek di controller, bukan di service)
      - Jika jenis='setor' → decrement saldo nasabah sebesar total_nilai
      - Jika jenis='tarik' → increment saldo nasabah sebesar total_nilai
      - Set transaksi.status = 'void'
      - Semua dalam DB::transaction()

3. Bind TransaksiService di AppServiceProvider:
   $this->app->singleton(TransaksiService::class);

Pastikan semua operasi atomic — jika salah satu gagal, semua di-rollback.
```

---

### PROMPT 7.2 — Form Setoran Sampah (Backend)

```
Baca PRD.md bagian "4.5 Transaksi Setoran Sampah" (SET-01 sampai SET-08).

Buat controller dan Form Request untuk transaksi setoran:

1. Buat app/Http/Requests/StoreSetoranRequest.php
   Rules:
   - nasabah_id: required, exists:nasabah,id
   - tanggal_transaksi: required, date, before_or_equal:today
   - catatan: nullable, string, max:500
   - details: required, array, min:1
   - details.*.jenis_sampah_id: required, exists:jenis_sampah,id, 
     (tambahkan rule custom: jenis sampah harus is_active=1)
   - details.*.berat: required, numeric, min:0.1
   
   Custom messages dalam Bahasa Indonesia.

2. Buat app/Http/Controllers/Admin/TransaksiController.php (dan Petugas juga)
   
   Untuk Admin & Petugas (gunakan shared controller atau 2 controller):
   - index(): daftar semua transaksi
     * Filter: jenis (setor/tarik), nasabah_id, tanggal_dari, tanggal_sampai
     * Eager load: nasabah, petugas
     * Paginate 20
   - createSetor(): form setoran
     * Kirimkan: list nasabah aktif, list jenis_sampah aktif (with kategori)
   - storeSetor(StoreSetoranRequest $request): 
     * Inject TransaksiService
     * Panggil $service->prosesSetoran($validated)
     * Redirect ke detail transaksi dengan flash success
   - createTarik(): form penarikan
   - storeTarik(StorePenarikanRequest $request):
     * Panggil $service->prosesPenarikan($validated)
     * Catch InsufficientBalanceException → redirect back dengan error
   - show(Transaksi $transaksi): detail transaksi + detail items
   - void(Transaksi $transaksi): (admin only)
     * Panggil $service->voidTransaksi($transaksi)
     * Redirect dengan flash

3. Daftarkan routes:
   GET  /admin/transaksi                → index
   GET  /admin/transaksi/setor/create   → createSetor
   POST /admin/transaksi/setor          → storeSetor
   GET  /admin/transaksi/tarik/create   → createTarik
   POST /admin/transaksi/tarik          → storeTarik
   GET  /admin/transaksi/{transaksi}    → show
   POST /admin/transaksi/{transaksi}/void → void
```

---

### PROMPT 7.3 — Form Setoran Sampah (Frontend - Alpine.js)

```
Baca PRD.md bagian "9. UI/UX Guidelines" (loading state, konfirmasi modal).

Buat tampilan form setoran sampah yang interaktif:

1. resources/views/admin/transaksi/setor/create.blade.php
   
   Bagian Header Form:
   - Dropdown pilih nasabah (searchable - ketik nama/no_rekening)
     Gunakan Alpine.js: saat nasabah dipilih, tampilkan info (nama, no_rekening, saldo saat ini)
   - Input tanggal transaksi (default: hari ini)
   - Textarea catatan (opsional)

   Bagian Detail Sampah (DYNAMIC ROWS dengan Alpine.js):
   - Tabel dengan baris yang bisa ditambah/dihapus
   - Kolom per baris: 
     * No. | Jenis Sampah (dropdown) | Satuan | Berat (input number) | 
       Harga/kg | Subtotal (auto-kalkulasi) | Hapus (tombol ×)
   - Saat dropdown jenis sampah dipilih → otomatis isi Harga/kg dari data
   - Saat berat diinput → otomatis hitung Subtotal (berat × harga)
   - Tombol "+ Tambah Sampah" untuk menambah baris baru
   - Minimal 1 baris, baris pertama tidak bisa dihapus

   Bagian Ringkasan:
   - Total Berat (auto-sum semua berat)
   - Total Nilai (auto-sum semua subtotal) — format Rupiah
   - Tombol "Simpan Setoran" (submit)
   - Tombol "Batal" (kembali ke daftar)

   Implementasi Alpine.js:
   ```html
   <div x-data="setoranForm()">
     <!-- gunakan x-for untuk render rows -->
     <!-- gunakan x-model untuk binding -->
     <!-- gunakan @change untuk trigger kalkulasi -->
   </div>
   <script>
   function setoranForm() {
     return {
       nasabah: null,
       rows: [{ jenis_sampah_id: '', berat: '', harga: 0, subtotal: 0 }],
       jenisSampahList: @json($jenisSampahList),
       // methods: addRow(), removeRow(), calculateSubtotal(), getTotalBerat(), getTotalNilai()
     }
   }
   </script>
   ```

2. resources/views/admin/transaksi/tarik/create.blade.php
   - Dropdown pilih nasabah (sama seperti setoran)
   - Tampilkan saldo nasabah yang dipilih (fetch via Alpine atau pass dari controller)
   - Input nominal penarikan (max = saldo saat ini)
   - Validasi client-side: nominal tidak boleh > saldo
   - Input tanggal + catatan opsional
   - Tombol Simpan

Pastikan semua data dikirim sebagai array dalam format yang sesuai dengan StoreSetoranRequest.
```

---

### PROMPT 7.4 — Detail & Riwayat Transaksi + Cetak Struk

```
Baca PRD.md bagian "4.5 SET-05, SET-06, SET-07, SET-08".

Buat halaman detail transaksi dan fitur cetak:

1. resources/views/admin/transaksi/index.blade.php
   - Filter bar di atas: pilih jenis (setor/tarik), rentang tanggal, cari nasabah
   - Tabel transaksi:
     Kode Transaksi | Nasabah | Jenis | Total Berat | Total Nilai | Tanggal | Status | Aksi
   - Badge jenis: "Setor" (hijau), "Tarik" (merah)
   - Badge status: "Aktif" (hijau), "Void" (abu-abu dengan strikethrough)
   - Paginasi

2. resources/views/admin/transaksi/show.blade.php
   - Header: Kode Transaksi, Tanggal, Status, Nasabah, Petugas
   - Card info nasabah (nama, no rekening, saldo setelah transaksi)
   - Tabel detail sampah (hanya jika jenis=setor):
     Jenis Sampah | Kategori | Berat | Harga/Satuan | Subtotal
   - Card total: Total Berat, Total Nilai
   - Tombol "Cetak Struk" → buka halaman print
   - Tombol "Void Transaksi" (hanya admin, hanya jika status=aktif) → modal konfirmasi
   - Tombol "Kembali"

3. resources/views/admin/transaksi/struk.blade.php (halaman cetak)
   - Desain struk kasir (lebar 80mm simulasi atau A5)
   - Header: Logo + Nama Bank Sampah + Alamat
   - Garis pembatas
   - Info: No. Struk, Tanggal, Jam, Kasir
   - Info Nasabah: Nama, No. Rekening
   - Detail item (jika setor) atau nominal (jika tarik)
   - Garis pembatas
   - Total Nilai
   - Saldo setelah transaksi
   - Footer: Terima kasih
   - CSS @media print:
     * Sembunyikan navbar dan sidebar
     * Auto-print saat halaman dibuka: window.onload = () => window.print()

4. Void Transaksi:
   - Modal konfirmasi dengan pesan peringatan bahwa saldo akan dikembalikan/dipotong
   - Form POST ke /admin/transaksi/{id}/void
   - Setelah void, tampilkan flash warning "Transaksi berhasil di-void. Saldo nasabah telah dikoreksi."
```

---

## FASE 8 — LAPORAN

---

### PROMPT 8.1 — Laporan Transaksi & Export

```
Baca PRD.md bagian "4.7 Laporan" (LAP-01 sampai LAP-06).

Buat sistem laporan lengkap:

1. Buat app/Services/LaporanService.php
   Methods:
   - getTransaksi(array $filters): Collection
     * Filter: tanggal_dari, tanggal_sampai, jenis, nasabah_id, status
     * Eager load: nasabah, petugas, details.jenisSampah
   - getRekapPeriode(string $periode, int $bulan, int $tahun): array
     * Periode: 'harian', 'bulanan', 'tahunan'
     * Return: total_setor, total_tarik, total_berat, jumlah_transaksi
   - getSaldoNasabah(): Collection
     * Semua nasabah aktif dengan saldo, diurutkan saldo tertinggi
   - getRekapJenisSampah(array $filters): Collection
     * Total berat dan nilai per jenis sampah dalam periode tertentu

2. Buat app/Http/Controllers/Admin/LaporanController.php
   - index(): halaman laporan dengan form filter
   - transaksi(Request $request): tampil hasil filter
   - saldoNasabah(): laporan saldo semua nasabah
   - exportPdf(Request $request): generate PDF menggunakan DomPDF
   - exportExcel(Request $request): generate Excel menggunakan Maatwebsite

3. Buat app/Exports/TransaksiExport.php (untuk Maatwebsite Excel)
   - Implement FromCollection, WithHeadings, WithMapping, WithStyles
   - Heading: No | Kode | Tanggal | Nasabah | Jenis | Berat (kg) | Nilai (Rp) | Status | Petugas
   - Format tanggal: d/m/Y
   - Format nilai: number format dengan separator ribuan

4. Buat resources/views/admin/laporan/index.blade.php
   - Form filter: Tanggal Dari, Tanggal Sampai, Jenis, Nasabah (dropdown), Status
   - Tombol "Tampilkan", "Export PDF", "Export Excel"
   - Tabel hasil laporan (sama seperti riwayat transaksi)
   - Card ringkasan di bawah filter:
     * Total Transaksi | Total Setoran | Total Penarikan | Total Berat

5. Buat resources/views/admin/laporan/pdf.blade.php (template DomPDF)
   - Header: Logo + Nama Bank Sampah + Judul Laporan + Periode
   - Tabel transaksi (tanpa aksi)
   - Footer: Ringkasan total + Tanggal cetak + Tanda tangan ruang

6. Buat resources/views/admin/laporan/saldo-nasabah.blade.php
   - Tabel: No | Nama | No. Rekening | No. HP | Saldo
   - Total saldo di bawah tabel
   - Tombol Export PDF + Excel

Daftarkan routes:
   GET /admin/laporan                → index
   GET /admin/laporan/transaksi      → transaksi
   GET /admin/laporan/saldo-nasabah  → saldoNasabah
   GET /admin/laporan/export/pdf     → exportPdf
   GET /admin/laporan/export/excel   → exportExcel
```

---

## FASE 9 — MANAJEMEN PENGGUNA

---

### PROMPT 9.1 — CRUD User Sistem

```
Baca PRD.md bagian "4.8 Manajemen Pengguna" (USR-01 sampai USR-05).

Buat manajemen user sistem (hanya admin):

1. Buat app/Http/Requests/StoreUserRequest.php
   - name: required, string, max:100
   - email: required, email, unique:users,email
   - password: required, string, min:8, confirmed
   - role: required, in:admin,petugas (TIDAK BISA buat user role nasabah dari sini)
   - is_active: boolean

2. Buat app/Http/Requests/UpdateUserRequest.php
   - name: required, string, max:100
   - email: required, email, unique:users,email (ignore current user)
   - password: nullable, string, min:8, confirmed (opsional saat update)
   - role: required, in:admin,petugas
   - is_active: boolean

3. Buat app/Http/Controllers/Admin/UserController.php
   - index(): daftar user (kecuali nasabah), filter by role, search
   - create(), store(), edit(), update()
   - toggleActive(User $user): toggle is_active
     * Cek jangan sampai admin menonaktifkan dirinya sendiri
   - resetPassword(User $user): reset ke password default "password123"
     * Hanya admin
     * Flash pesan bahwa password baru adalah "password123"

4. Buat views:
   resources/views/admin/users/index.blade.php
   - Tabel: Nama | Email | Role | Status | Terakhir Login | Aksi
   - Badge role dengan warna berbeda
   - Tombol Edit + Toggle + Reset Password

   resources/views/admin/users/create.blade.php
   resources/views/admin/users/edit.blade.php
   - Form user dengan field di atas

5. Daftarkan routes:
   Route::resource('/users', UserController::class)->except(['show', 'destroy'])
   Route::patch('/users/{user}/toggle', ...)->name('users.toggle')
   Route::post('/users/{user}/reset-password', ...)->name('users.reset-password')
```

---

## FASE 10 — PANEL NASABAH

---

### PROMPT 10.1 — Self-Service Panel Nasabah

```
Baca PRD.md bagian "4.10 Panel Nasabah".

Buat panel khusus untuk nasabah (self-service):

1. Buat app/Http/Controllers/Nasabah/RiwayatController.php
   - index(): 
     * Ambil user yang login → cari data nasabah terkait
     * Ambil semua transaksi nasabah ini (paginate 10)
     * Filter by jenis (setor/tarik) dan tanggal
   - show(Transaksi $transaksi): detail transaksi (hanya milik nasabah ini!)

2. Buat app/Http/Controllers/Nasabah/ProfilController.php
   - edit(): form edit profil nasabah
   - update(Request $request): update nama, alamat, no_hp, foto
     * TIDAK BOLEH edit NIK atau no_rekening
   - changePassword(Request $request): ganti password user terkait

3. Buat views:
   resources/views/nasabah/dashboard.blade.php
   - Card saldo besar di tengah (seperti kartu ATM)
   - Nomor rekening
   - Card statistik: Setoran bulan ini, Penarikan bulan ini
   - Tabel 5 transaksi terakhir

   resources/views/nasabah/riwayat/index.blade.php
   - Filter jenis + tanggal
   - Tabel riwayat transaksi nasabah ini saja
   - Setiap baris bisa diklik untuk lihat detail

   resources/views/nasabah/riwayat/show.blade.php
   - Detail transaksi (read-only)
   - Tombol cetak struk

   resources/views/nasabah/profil/edit.blade.php
   - Form edit profil (terbatas)
   - Tampilkan no_rekening sebagai read-only info

4. Policy untuk keamanan:
   Buat app/Policies/TransaksiPolicy.php
   - Method view(): nasabah hanya bisa lihat transaksi miliknya sendiri
   - Gunakan $this->authorize('view', $transaksi) di controller

5. Daftarkan routes:
   GET /nasabah/dashboard        → nasabah.dashboard
   GET /nasabah/riwayat          → nasabah.riwayat.index
   GET /nasabah/riwayat/{id}     → nasabah.riwayat.show
   GET /nasabah/profil/edit      → nasabah.profil.edit
   PUT /nasabah/profil           → nasabah.profil.update
   PUT /nasabah/profil/password  → nasabah.profil.password
```

---

## FASE 11 — PENGATURAN & PROFIL

---

### PROMPT 11.1 — Pengaturan Sistem & Profil Admin

```
Baca PRD.md bagian "4.9 Profil & Pengaturan".

Buat halaman pengaturan sistem dan edit profil:

1. Buat app/Http/Controllers/Admin/PengaturanController.php
   - index(): tampil form pengaturan dengan data dari app_settings
   - update(Request $request):
     * Update nama_bank_sampah, alamat, no_telp, email, tahun_berdiri
     * Handle upload logo (simpan di storage/app/public/logo/)
     * Gunakan AppSetting::set() untuk menyimpan
     * Flash success + redirect back

2. Buat app/Http/Controllers/ProfilController.php (semua role bisa akses)
   - edit(): tampil form edit profil (name, email, foto profil)
   - update(Request $request): update data user
   - changePassword(Request $request):
     * Validasi: current_password harus benar (cek Hash::check)
     * password baru: required, min:8, confirmed
     * Update password

3. Buat views:
   resources/views/admin/pengaturan/index.blade.php
   - Form pengaturan:
     * Nama Bank Sampah
     * Alamat
     * No. Telp
     * Email
     * Tahun Berdiri
     * Upload Logo (dengan preview)
   - Preview logo saat ini
   - Tombol Simpan Pengaturan

   resources/views/profil/edit.blade.php (shared semua role)
   - Tab 1: Edit Profil (nama, email, foto)
   - Tab 2: Ganti Password
   - Implementasi tab dengan Alpine.js

4. Buat app/Helpers/helpers.php (jika belum ada):
   - function appSetting(string $key, $default = null) → shortcut ke AppSetting::get()
   - function formatRupiah(int|float $angka): string → "Rp 1.500.000"
   - Daftarkan di composer.json di autoload.files

5. Buat Blade Directive di AppServiceProvider:
   Blade::directive('rupiah', function ($expression) {
       return "<?php echo formatRupiah($expression); ?>";
   });
   Penggunaan: @rupiah($nasabah->saldo)

6. Daftarkan routes:
   GET  /admin/pengaturan    → pengaturan.index
   PUT  /admin/pengaturan    → pengaturan.update
   GET  /profil/edit         → profil.edit
   PUT  /profil              → profil.update
   PUT  /profil/password     → profil.password
```

---

## FASE 12 — POLISH & FINALISASI

---

### PROMPT 12.1 — Error Handling & UX Polish

```
Baca PRD.md bagian "9. UI/UX Guidelines" dan Agent.md bagian "Checklist Keamanan".

Lakukan polish dan penyempurnaan:

1. Custom Error Pages:
   Buat resources/views/errors/403.blade.php — Akses Ditolak (dengan branding)
   Buat resources/views/errors/404.blade.php — Halaman Tidak Ditemukan
   Buat resources/views/errors/500.blade.php — Kesalahan Server

2. Global Exception Handler (bootstrap/app.php atau Handler):
   - Tangkap InsufficientBalanceException → redirect back dengan error yang user-friendly
   - Tangkap ModelNotFoundException → 404 dengan pesan custom

3. Komponen Flash Message yang lebih baik:
   - Auto-dismiss setelah 5 detik menggunakan Alpine.js
   - Tombol close (×) manual
   - Animasi slide-in dari atas

4. Modal Konfirmasi Global (Alpine.js):
   Buat resources/views/components/confirm-modal.blade.php
   - Reusable modal konfirmasi untuk delete/void/toggle
   - Props: title, message, action (URL), method
   - Trigger dari tombol dengan data attributes

5. Loading State:
   - Submit button: disabled + text berubah saat form disubmit
   - Gunakan Alpine.js: @submit="loading = true" dan x-bind:disabled="loading"

6. Perbaiki responsiveness:
   - Sidebar collapse di mobile (hamburger menu)
   - Tabel scrollable horizontal di mobile (overflow-x-auto)
   - Form full width di mobile

7. Breadcrumb navigation:
   Tambahkan breadcrumb di setiap halaman menggunakan @section('breadcrumb')
   Contoh: Dashboard > Nasabah > Tambah Nasabah

8. Empty State Component:
   Buat resources/views/components/empty-state.blade.php
   - Tampil ketika tabel/list kosong
   - Props: message, icon, action_text, action_url
```

---

### PROMPT 12.2 — Seed Data Demo & Testing

```
Baca Agent.md bagian "Checklist Keamanan".

Buat data demo yang realistis dan lakukan testing menyeluruh:

1. Buat database/seeders/DemoSeeder.php
   Data yang perlu dibuat:
   - 20 nasabah dengan data realistis (Faker dengan locale id_ID)
   - 50 transaksi setoran (distribusi 3 bulan terakhir)
     * Random nasabah, random 2-4 jenis sampah per transaksi
     * Berat random antara 0.5 - 10 kg per jenis
   - 15 transaksi penarikan (distribusi acak)
     * Pastikan nominal tidak melebihi saldo nasabah
   - 2 transaksi yang divoid (untuk test tampilan)

2. Update DatabaseSeeder.php:
   - Panggil DemoSeeder::class setelah seeder awal

3. Jalankan: php artisan migrate:fresh --seed

4. Lakukan testing manual untuk setiap acceptance criteria dari PRD.md:
   
   TEST LOGIN:
   [ ] Login admin@banksampah.test → redirect ke /admin/dashboard
   [ ] Login petugas@banksampah.test → redirect ke /petugas/dashboard
   [ ] Login nasabah@banksampah.test → redirect ke /nasabah/dashboard
   [ ] Login dengan password salah → error message muncul
   [ ] Akses /admin/dashboard sebagai petugas → 403

   TEST NASABAH:
   [ ] Tambah nasabah baru → no_rekening auto-generate (BS-YYYY-XXXXX)
   [ ] Edit nasabah → data tersimpan
   [ ] Toggle status → status berubah, akun tidak bisa login
   [ ] Detail nasabah → tampilkan saldo + riwayat

   TEST TRANSAKSI:
   [ ] Setor sampah dengan 3 jenis → saldo nasabah bertambah sesuai total
   [ ] Tarik saldo normal → saldo berkurang
   [ ] Tarik saldo melebihi saldo → error muncul, saldo tidak berubah
   [ ] Void transaksi setor → saldo nasabah kembali berkurang
   [ ] Void transaksi tarik → saldo nasabah kembali bertambah
   [ ] Cetak struk → halaman cetak terbuka, layout benar

   TEST LAPORAN:
   [ ] Filter transaksi by tanggal → hasil sesuai filter
   [ ] Export PDF → file terdownload, isi benar
   [ ] Export Excel → file terdownload, isi benar

5. Laporkan apabila ada bug atau inkonsistensi data.
```

---

### PROMPT 12.3 — Optimasi & Keamanan Final

```
Baca Agent.md bagian "Checklist Keamanan" dan "Catatan Pengembangan".

Lakukan pengecekan keamanan dan optimasi performa:

1. KEAMANAN — Cek dan perbaiki:
   [ ] Semua route memiliki middleware auth
   [ ] Form menggunakan @csrf
   [ ] Mass assignment: semua Model punya $fillable yang tepat
   [ ] File upload divalidasi (MIME type + max size)
   [ ] Nasabah menggunakan SoftDeletes
   [ ] Harga di detail_transaksi adalah snapshot (bukan relasi ke harga aktif)
   [ ] Nasabah tidak bisa akses transaksi orang lain (cek Policy)
   [ ] Admin tidak bisa nonaktifkan dirinya sendiri

2. QUERY OPTIMIZATION:
   [ ] Semua query yang ada relasi menggunakan eager loading (with())
   [ ] Halaman daftar menggunakan paginate(), bukan get() semua
   [ ] Tambahkan index database:
       - nasabah: no_rekening, status, registered_by
       - transaksi: nasabah_id, petugas_id, jenis, status, tanggal_transaksi
       - detail_transaksi: transaksi_id, jenis_sampah_id
   
   Buat migration untuk tambah index:
   php artisan make:migration add_indexes_to_tables

3. PERBAIKAN MINOR:
   [ ] Title halaman dinamis (setiap halaman punya title yang berbeda)
   [ ] Meta description di head
   [ ] Favicon menggunakan logo bank sampah
   [ ] Format tanggal konsisten: d F Y (contoh: 10 November 2026)
   [ ] Format jam: H:i WIB

4. CONFIG & ENV:
   [ ] Semua nilai yang bisa berubah ada di .env atau AppSetting
   [ ] Pastikan .env.example diupdate
   [ ] Tambahkan APP_TIMEZONE=Asia/Jakarta di .env

5. DOKUMENTASI:
   [ ] Update README.md dengan instruksi instalasi lengkap
   [ ] Dokumentasi akun demo (email + password)
   [ ] Screenshot dashboard (opsional)

6. Jalankan final check:
   php artisan optimize:clear
   php artisan route:list (pastikan semua route benar)
   php artisan migrate:status (pastikan semua migration sudah dijalankan)
```

---

## FASE 13 — MULTI-UNIT BANK SAMPAH (DUSUN/RW)

---

### PROMPT 13.1 — Migration & Backend Manajemen Unit

```
Baca PRD.md bagian "4.11 Manajemen Unit Bank Sampah" dan "5. Desain Database".
Baca Agent.md bagian "Struktur Peran & Akses".

Buat sistem multi-unit bank sampah:

1. Buat migration untuk tabel units dan relasi unit_id:
   php artisan make:migration create_units_and_add_unit_id_to_tables
   - Buat tabel `units`:
     * id, kode_unit (varchar 20, unique), nama (varchar 100), alamat (text nullable),
       nama_ketua (varchar 100 nullable), no_hp (varchar 20 nullable),
       status (enum: aktif, nonaktif default aktif), timestamps
   - Tambah kolom `unit_id` ke tabel:
     * `users`: foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete()
     * `nasabah`: foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete()
     * `transaksi`: foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete()

2. Buat Model app/Models/Unit.php:
   - $fillable: kode_unit, nama, alamat, nama_ketua, no_hp, status
   - Relasi:
     * users(): HasMany
     * nasabah(): HasMany
     * transaksi(): HasMany
     * scopeAktif($query)

3. Update Model eksisting:
   - User: relasi unit(): BelongsTo
   - Nasabah: relasi unit(): BelongsTo
   - Transaksi: relasi unit(): BelongsTo

4. Buat app/Http/Requests/StoreUnitRequest.php & UpdateUnitRequest.php:
   - kode_unit: required, string, max:20, unique:units,kode_unit
   - nama: required, string, max:100
   - alamat: nullable, string
   - nama_ketua: nullable, string, max:100
   - no_hp: nullable, string, max:20
   - status: required, in:aktif,nonaktif

5. Update User Management:
   - Form create/edit user petugas dapat memilih penugasan Unit (`unit_id`).

6. Update TransaksiService & NasabahService:
   - Saat nasabah didaftarkan oleh petugas, otomatis terikat pada `unit_id` petugas (atau pilihan unit jika didaftarkan admin).
   - Saat transaksi setor/tarik dicatat, `unit_id` transaksi otomatis diisi sesuai unit nasabah / petugas.

7. Daftarkan routes di routes/web.php (grup admin):
   - Route::resource('/units', UnitController::class);
   - Route::patch('/units/{unit}/toggle', [UnitController::class, 'toggle'])->name('units.toggle');
```

---

### PROMPT 13.2 — Frontend Manajemen Unit & Scope Filtering Petugas

```
1. Buat view Admin untuk Master Data Unit:
   - resources/views/admin/units/index.blade.php (tabel daftar unit, jumlah nasabah, jumlah transaksi, status, aksi)
   - resources/views/admin/units/create.blade.php (form tambah unit)
   - resources/views/admin/units/edit.blade.php (form edit unit)

2. Tambahkan menu "Unit Bank Sampah" di sidebar navigasi Admin (resources/views/layouts/partials/sidebar.blade.php).

3. Penerapan Scope Filtering Data:
   - Jika login sebagai Petugas Unit (memiliki unit_id):
     * Daftar Nasabah otomatis terfilter hanya nasabah unitnya.
     * Daftar Transaksi otomatis terfilter hanya transaksi unitnya.
     * Dashboard Petugas menampilkan statistik unit yang bersangkutan.
   - Jika login sebagai Admin / Pengelola Desa:
     * Tampilkan dropdown filter unit di halaman Daftar Nasabah, Daftar Transaksi, dan Laporan.
     * Tampilkan label/badge Unit pada setiap baris data nasabah dan transaksi.
```

---

## FASE 14 — MANAJEMEN PENGEPUL, PENJUALAN SAMPAH DESA & STOK GUDANG

---

### PROMPT 14.1 — Backend Pengepul & Penjualan Sampah ke Pengepul

```
Baca PRD.md bagian "4.12 Manajemen Pengepul & Penjualan Sampah (Level Pengelola Desa)".

1. Buat migration:
   php artisan make:migration create_pengepul_and_penjualan_sampah_tables
   - Tabel `pengepul`:
     * id, nama_perusahaan (varchar 100), penanggung_jawab (varchar 100), no_hp (varchar 20),
       alamat (text nullable), status (enum: aktif, nonaktif default aktif), timestamps
   - Tabel `penjualan_sampah`:
     * id, kode_penjualan (varchar 30 unique), pengepul_id (FK ke pengepul),
       tanggal_penjualan (date), total_berat (decimal 10,3), total_pendapatan (decimal 15,2),
       status_pembayaran (enum: lunas, piutang default lunas),
       pengelola_id (FK ke users), catatan (text nullable), timestamps
   - Tabel `detail_penjualan_sampah`:
     * id, penjualan_id (FK cascade), jenis_sampah_id (FK),
       berat (decimal 10,3), harga_jual_satuan (decimal 10,2), subtotal (decimal 15,2), timestamps

2. Buat Models & Relationships:
   - app/Models/Pengepul.php (relasi penjualan(): HasMany)
   - app/Models/PenjualanSampah.php (relasi pengepul(), pengelola(), details(): HasMany)
   - app/Models/DetailPenjualanSampah.php (relasi penjualan(), jenisSampah())

3. Buat Service app/Services/PenjualanService.php:
   - Method getStokSampahGudang():
     * Hitung total berat masuk dari setoran warga (status aktif) dikurangi total berat yang telah terjual ke pengepul per jenis sampah.
   - Method simpanPenjualan(array $data): PenjualanSampah:
     * DB::transaction:
     * Validasi berat yang dijual tidak boleh melebihi sisa stok di gudang desa.
     * Generate kode_penjualan (format: JUAL-YYYYMMDD-XXXXX).
     * Simpan master penjualan_sampah & detail_penjualan_sampah.

4. Controller & Form Requests:
   - Admin/PengepulController.php (CRUD mitra pengepul)
   - Admin/PenjualanSampahController.php (index, create, store, show, suratJalan)
```

---

### PROMPT 14.2 — Frontend Penjualan Sampah & Monitoring Stok Gudang

```
1. Buat view Admin untuk Master Pengepul:
   - resources/views/admin/pengepul/index.blade.php
   - resources/views/admin/pengepul/create.blade.php
   - resources/views/admin/pengepul/edit.blade.php

2. Buat view Monitoring Stok Gudang Desa:
   - resources/views/admin/penjualan/stok.blade.php
   - Tampilkan kartu indikator: Total Berat Sampah Masuk, Total Berat Terjual, Sisa Stok Tersedia (kg).
   - Tabel stok per jenis sampah: Nama Kategori, Nama Jenis, Total Masuk (kg), Total Terjual (kg), Stok Siap Jual (kg), Estimasi Nilai Jual.

3. Buat view Transaksi Penjualan Sampah:
   - resources/views/admin/penjualan/index.blade.php (riwayat penjualan ke pengepul)
   - resources/views/admin/penjualan/create.blade.php:
     * Formulir dinamis (Alpine.js): Pilih Pengepul, Tanggal, dan Baris Item Sampah yang dijual.
     * Tampilkan batas stok saat memilih jenis sampah.
     * Auto-kalkulasi total bobot dan total pendapatan kotor.
   - resources/views/admin/penjualan/show.blade.php (detail penjualan)
   - resources/views/admin/penjualan/surat-jalan.blade.php (nota/surat jalan print-friendly)

4. Tambahkan menu di sidebar admin:
   - "Mitra Pengepul" & "Penjualan (Gudang Desa)"
```

---

### PROMPT 14.3 — Laporan Penjualan & Laba/Rugi Operasional Desa

```
1. Buat fitur Laporan Penjualan Sampah ke Pengepul:
   - Filter tanggal, pengepul, status pembayaran.
   - Ekspor PDF & Excel laporan penjualan.

2. Buat Laporan Laba/Rugi Operasional Bank Sampah Desa:
   - Komponen Laba Kotor:
     * Total Pendapatan Penjualan ke Pengepul (Kredit Kas Operasional).
     * Total Beban Pembelian dari Nasabah (Debit Kas Operasional).
     * Laba/Margin Operasional Bersih (Pendapatan - Beban).
   - View: resources/views/admin/laporan/laba-rugi.blade.php
   - Tombol Ekspor PDF & Excel untuk Laporan Laba/Rugi Desa.
```

---

## RINGKASAN URUTAN PROMPT

| No | Prompt | Fase | Output |
|----|--------|------|--------|
| 0.1 | Instalasi Laravel 11 | Setup | Project Laravel berjalan |
| 0.2 | Install Package Composer | Setup | Semua package terinstall |
| 0.3 | Setup Frontend | Setup | TailwindCSS + DaisyUI ready |
| 1.1 | Buat Semua Migration | Database | 8 tabel terbuat di MySQL |
| 1.2 | Buat Semua Model | Database | Model + Relationship lengkap |
| 1.3 | Buat Seeder | Database | Data awal + master data |
| 2.1 | Role Middleware & Auth | Auth | Login + redirect per role |
| 3.1 | Layout Utama | UI | Sidebar + topbar + flash message |
| 4.1 | Dashboard Admin | Dashboard | Stats + Chart.js |
| 4.2 | Dashboard Petugas & Nasabah | Dashboard | 2 dashboard tambahan |
| 5.1 | CRUD Nasabah Backend | Nasabah | Controller + Service + Request |
| 5.2 | CRUD Nasabah Frontend | Nasabah | 4 view halaman nasabah |
| 6.1 | CRUD Kategori Sampah | Sampah | CRUD lengkap kategori |
| 6.2 | CRUD Jenis Sampah | Sampah | CRUD + log harga |
| 7.1 | Service Layer Transaksi | Transaksi | TransaksiService + Exception |
| 7.2 | Form Setoran Backend | Transaksi | Controller + Request |
| 7.3 | Form Setoran Frontend | Transaksi | Alpine.js dynamic form |
| 7.4 | Detail & Cetak Struk | Transaksi | Show + print view |
| 8.1 | Laporan & Export | Laporan | PDF + Excel export |
| 9.1 | CRUD User Sistem | User | Manajemen user admin |
| 10.1 | Panel Nasabah | Nasabah | Self-service portal |
| 11.1 | Pengaturan & Profil | Settings | App settings + profil |
| 12.1 | Error Handling & Polish | Polish | UX polish, error pages |
| 12.2 | Seed Demo & Testing | Testing | Data demo + test checklist |
| 12.3 | Optimasi & Keamanan | Final | Security + performance |
| 13.1 | Multi-Unit Backend & Migration | Multi-Unit | Tabel units, relasi unit_id, controller |
| 13.2 | Multi-Unit Frontend & Scope | Multi-Unit | UI unit, role scope filtering, sidebar |
| 14.1 | Penjualan Sampah Backend | Penjualan | Pengepul, Penjualan, Stok service |
| 14.2 | Penjualan Sampah Frontend & Stok | Penjualan | UI form jual, surat jalan, monitor stok |
| 14.3 | Laporan Penjualan & Laba Rugi | Laporan | PDF/Excel jual & laba rugi operasional |

---

> **Catatan Penting:**
> - Setiap prompt mengasumsikan prompt sebelumnya telah selesai dieksekusi
> - Jika ada error di satu langkah, selesaikan dulu sebelum lanjut ke prompt berikutnya  
> - Selalu jalankan `php artisan optimize:clear` setelah mengubah config atau route
> - Gunakan `php artisan migrate:fresh --seed` untuk reset database saat development

