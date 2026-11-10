# PRD — Sistem Informasi Bank Sampah BAA

> **Product Requirements Document (PRD)**  
> Versi: 1.0.0  
> Tanggal: 10 November 2026  
> Referensi Proyek Lama: https://github.com/theydoet666/banksampah  
> Tech Stack Baru: **Laravel 11 + MySQL + Blade + TailwindCSS**

---

## 1. Latar Belakang & Tujuan

Bank Sampah BAA adalah sistem pengelolaan sampah berbasis tabungan yang memungkinkan masyarakat (nasabah) menyetorkan sampah dan menukarnya menjadi saldo rupiah. Sistem ini sebelumnya dibangun menggunakan PHP Native. Versi baru ini dibangun ulang menggunakan **Laravel** agar lebih terstruktur, aman, dan mudah dikembangkan.

### Tujuan Utama
- Mendigitalisasi pencatatan transaksi setoran dan penarikan sampah
- Mempermudah pengelolaan data nasabah oleh petugas
- Menyediakan laporan keuangan dan operasional yang akurat
- Meningkatkan transparansi saldo tabungan nasabah

---

## 2. Pemangku Kepentingan (Stakeholders)

| Role | Deskripsi |
|------|-----------|
| **Admin** | Superadmin sistem — kelola semua data termasuk user, harga sampah, dan laporan |
| **Petugas** | Operator lapangan — input transaksi setoran dan penarikan |
| **Nasabah** | Anggota/warga — lihat saldo dan riwayat transaksi pribadi |

---

## 3. Ruang Lingkup (Scope)

### Dalam Lingkup (In Scope)
- Autentikasi & manajemen pengguna (multi-role)
- Manajemen data nasabah
- Manajemen jenis & kategori sampah + harga
- Transaksi setoran sampah (dengan detail multi-item)
- Transaksi penarikan saldo
- Dashboard dengan statistik real-time
- Laporan dengan ekspor PDF & Excel
- Profil & pengaturan sistem

### Di Luar Lingkup (Out of Scope)
- Integrasi pembayaran digital (e-wallet, transfer bank)
- Aplikasi mobile native (Android/iOS)
- Sistem notifikasi SMS/WhatsApp (fase berikutnya)
- Penjualan sampah ke pengepul (fase berikutnya)

---

## 4. Fitur & Modul

### 4.1 Autentikasi
| ID | Fitur | Prioritas |
|----|-------|-----------|
| AUTH-01 | Login dengan email & password | P0 |
| AUTH-02 | Logout | P0 |
| AUTH-03 | Remember me (session persisten) | P1 |
| AUTH-04 | Reset password via email | P1 |
| AUTH-05 | Role-based access control (Admin, Petugas, Nasabah) | P0 |

### 4.2 Dashboard
| ID | Fitur | Prioritas |
|----|-------|-----------|
| DASH-01 | Total nasabah aktif | P0 |
| DASH-02 | Total sampah masuk (kg) bulan ini | P0 |
| DASH-03 | Total nilai setoran & penarikan bulan ini | P0 |
| DASH-04 | Grafik transaksi 7 hari terakhir (Chart.js) | P1 |
| DASH-05 | Daftar 5 transaksi terbaru | P1 |
| DASH-06 | Jumlah jenis sampah aktif | P1 |

### 4.3 Manajemen Nasabah
| ID | Fitur | Prioritas |
|----|-------|-----------|
| NAS-01 | Daftar nasabah (paginated, searchable) | P0 |
| NAS-02 | Tambah nasabah baru (dengan auto-generate nomor rekening) | P0 |
| NAS-03 | Edit data nasabah | P0 |
| NAS-04 | Nonaktifkan / aktifkan nasabah (soft delete) | P0 |
| NAS-05 | Detail nasabah (saldo + riwayat transaksi) | P0 |
| NAS-06 | Cetak kartu nasabah (PDF) | P2 |
| NAS-07 | Import nasabah dari Excel | P2 |

### 4.4 Manajemen Jenis Sampah
| ID | Fitur | Prioritas |
|----|-------|-----------|
| SAM-01 | Daftar jenis sampah (dengan kategori & harga saat ini) | P0 |
| SAM-02 | Tambah jenis sampah baru | P0 |
| SAM-03 | Edit jenis sampah & update harga | P0 |
| SAM-04 | Nonaktifkan jenis sampah | P0 |
| SAM-05 | Riwayat perubahan harga per jenis sampah | P1 |
| SAM-06 | Kategori sampah (Plastik, Kertas, Logam, Kaca, Organik, dll.) | P0 |

### 4.5 Transaksi Setoran Sampah
| ID | Fitur | Prioritas |
|----|-------|-----------|
| SET-01 | Form input setoran (pilih nasabah, tanggal, detail sampah) | P0 |
| SET-02 | Multi-item dalam satu transaksi (tambah/hapus baris) | P0 |
| SET-03 | Auto-kalkulasi nilai berdasarkan berat x harga saat ini | P0 |
| SET-04 | Konfirmasi & simpan transaksi (saldo nasabah bertambah) | P0 |
| SET-05 | Cetak struk transaksi setoran (print-friendly / PDF) | P1 |
| SET-06 | Daftar riwayat seluruh setoran (filter tanggal, nasabah) | P0 |
| SET-07 | Detail transaksi setoran | P0 |
| SET-08 | Batalkan / void transaksi (admin only) | P1 |

### 4.6 Transaksi Penarikan Saldo
| ID | Fitur | Prioritas |
|----|-------|-----------|
| TAR-01 | Form input penarikan (pilih nasabah, nominal) | P0 |
| TAR-02 | Validasi saldo tidak boleh minus | P0 |
| TAR-03 | Konfirmasi & simpan (saldo nasabah berkurang) | P0 |
| TAR-04 | Cetak bukti penarikan (print-friendly / PDF) | P1 |
| TAR-05 | Daftar riwayat seluruh penarikan | P0 |
| TAR-06 | Batalkan / void penarikan (admin only) | P1 |

### 4.7 Laporan
| ID | Fitur | Prioritas |
|----|-------|-----------|
| LAP-01 | Laporan transaksi (filter: tanggal, jenis, nasabah) | P0 |
| LAP-02 | Laporan rekap per periode (harian, bulanan, tahunan) | P0 |
| LAP-03 | Laporan saldo seluruh nasabah | P0 |
| LAP-04 | Ekspor PDF (DomPDF / Barryvdh) | P1 |
| LAP-05 | Ekspor Excel (Maatwebsite/Excel) | P1 |
| LAP-06 | Laporan jenis sampah (total berat & nilai per jenis) | P1 |

### 4.8 Manajemen Pengguna (Admin)
| ID | Fitur | Prioritas |
|----|-------|-----------|
| USR-01 | Daftar pengguna sistem | P0 |
| USR-02 | Tambah pengguna (admin/petugas) | P0 |
| USR-03 | Edit pengguna | P0 |
| USR-04 | Nonaktifkan pengguna | P0 |
| USR-05 | Reset password pengguna | P1 |

### 4.9 Profil & Pengaturan
| ID | Fitur | Prioritas |
|----|-------|-----------|
| PROF-01 | Edit profil sendiri (nama, email, foto) | P1 |
| PROF-02 | Ganti password | P1 |
| PROF-03 | Pengaturan umum sistem (nama bank sampah, alamat, logo) | P1 |

### 4.10 Panel Nasabah (Self-service)
| ID | Fitur | Prioritas |
|----|-------|-----------|
| PNS-01 | Lihat saldo tabungan sendiri | P0 |
| PNS-02 | Riwayat setoran & penarikan sendiri | P0 |
| PNS-03 | Edit profil nasabah sendiri | P1 |

---

## 5. Desain Database

### ERD Overview

```
users ─────────────────────── nasabah
  |                              |
  | (petugas_id)                 | (nasabah_id)
  |                              |
  └──────── transaksi ──────────┘
                 |
                 | (transaksi_id)
                 |
           detail_transaksi
                 |
                 | (jenis_sampah_id)
                 |
           jenis_sampah ──── kategori_sampah
                 |
           harga_sampah_log
```

### Skema Tabel

#### `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(100) UNIQUE | |
| password | VARCHAR(255) | bcrypt |
| role | ENUM('admin','petugas','nasabah') | |
| is_active | BOOLEAN DEFAULT 1 | |
| email_verified_at | TIMESTAMP NULL | |
| remember_token | VARCHAR(100) NULL | |
| created_at / updated_at | TIMESTAMP | |

#### `nasabah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| user_id | BIGINT FK → users (nullable) | Jika punya akun login |
| no_rekening | VARCHAR(20) UNIQUE | Auto-generated |
| nama | VARCHAR(100) | |
| nik | VARCHAR(16) UNIQUE NULL | |
| tempat_lahir | VARCHAR(100) NULL | |
| tanggal_lahir | DATE NULL | |
| jenis_kelamin | ENUM('L','P') | |
| alamat | TEXT | |
| no_hp | VARCHAR(20) | |
| foto | VARCHAR(255) NULL | |
| saldo | DECIMAL(15,2) DEFAULT 0 | Saldo tabungan saat ini |
| status | ENUM('aktif','nonaktif') DEFAULT 'aktif' | |
| registered_by | BIGINT FK → users | Petugas yang daftar |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULL | Soft delete |

#### `kategori_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| nama | VARCHAR(100) | Plastik, Kertas, Logam, dll |
| deskripsi | TEXT NULL | |
| created_at / updated_at | TIMESTAMP | |

#### `jenis_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| kategori_id | BIGINT FK → kategori_sampah | |
| nama | VARCHAR(100) | Misal: Botol PET, Kardus, dll |
| satuan | ENUM('kg','gram') DEFAULT 'kg' | |
| harga_per_satuan | DECIMAL(10,2) | Harga beli saat ini |
| deskripsi | TEXT NULL | |
| is_active | BOOLEAN DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

#### `harga_sampah_log`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| jenis_sampah_id | BIGINT FK → jenis_sampah | |
| harga_lama | DECIMAL(10,2) | |
| harga_baru | DECIMAL(10,2) | |
| changed_by | BIGINT FK → users | |
| created_at | TIMESTAMP | |

#### `transaksi`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| kode_transaksi | VARCHAR(30) UNIQUE | Auto: TRX-YYYYMMDD-XXXXX |
| nasabah_id | BIGINT FK → nasabah | |
| jenis | ENUM('setor','tarik') | |
| total_berat | DECIMAL(10,3) NULL | Hanya untuk setoran |
| total_nilai | DECIMAL(15,2) | Total rupiah |
| catatan | TEXT NULL | |
| status | ENUM('aktif','void') DEFAULT 'aktif' | |
| petugas_id | BIGINT FK → users | |
| tanggal_transaksi | DATE | |
| created_at / updated_at | TIMESTAMP | |

#### `detail_transaksi`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| transaksi_id | BIGINT FK → transaksi | |
| jenis_sampah_id | BIGINT FK → jenis_sampah | |
| berat | DECIMAL(10,3) | Dalam satuan sesuai jenis_sampah.satuan |
| harga_satuan | DECIMAL(10,2) | Harga snapshot saat transaksi |
| subtotal | DECIMAL(15,2) | berat x harga_satuan |
| created_at / updated_at | TIMESTAMP | |

#### `app_settings`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| key | VARCHAR(100) UNIQUE | |
| value | TEXT | |
| created_at / updated_at | TIMESTAMP | |

---

## 6. Arsitektur Teknis

### Tech Stack
| Layer | Teknologi |
|-------|-----------|
| Framework | Laravel 11 |
| Database | MySQL 8.x |
| Template Engine | Blade |
| CSS Framework | TailwindCSS v3 + DaisyUI |
| JS Library | Alpine.js (interaktivitas ringan), Chart.js |
| Authentication | Laravel Breeze |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/excel (PhpSpreadsheet) |
| File Storage | Laravel Storage (local disk) |
| Authorization | Laravel Gates & Policies (RBAC) |

### Struktur Direktori (Laravel)
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── NasabahController.php
│   │   │   ├── JenisSampahController.php
│   │   │   ├── KategoriSampahController.php
│   │   │   ├── TransaksiController.php
│   │   │   ├── LaporanController.php
│   │   │   └── UserController.php
│   │   └── Nasabah/
│   │       └── DashboardController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── NasabahRequest.php
│       ├── JenisSampahRequest.php
│       └── TransaksiRequest.php
├── Models/
│   ├── User.php
│   ├── Nasabah.php
│   ├── KategoriSampah.php
│   ├── JenisSampah.php
│   ├── HargaSampahLog.php
│   ├── Transaksi.php
│   ├── DetailTransaksi.php
│   └── AppSetting.php
├── Policies/
│   ├── NasabahPolicy.php
│   └── TransaksiPolicy.php
└── Services/
    ├── TransaksiService.php
    ├── LaporanService.php
    └── NasabahService.php

database/
├── migrations/
│   ├── create_users_table.php
│   ├── create_nasabah_table.php
│   ├── create_kategori_sampah_table.php
│   ├── create_jenis_sampah_table.php
│   ├── create_harga_sampah_log_table.php
│   ├── create_transaksi_table.php
│   ├── create_detail_transaksi_table.php
│   └── create_app_settings_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    ├── KategoriSampahSeeder.php
    └── JenisSampahSeeder.php
```

---

## 7. Alur Proses Bisnis

### 7.1 Alur Setoran Sampah
```
Nasabah datang
      |
      v
Petugas buka Form Setoran
      |
      v
Pilih Nasabah (cari by nama / no_rekening)
      |
      v
Input Detail Sampah (jenis, berat) → sistem auto-hitung subtotal
      |
      v
Tambah baris sampah lain? --YES--> Ulangi input
      |NO
      v
Konfirmasi Total Nilai
      |
      v
Simpan → Saldo Nasabah BERTAMBAH → Log Transaksi
      |
      v
Cetak Struk (opsional)
```

### 7.2 Alur Penarikan Saldo
```
Nasabah minta tarik saldo
      |
      v
Petugas buka Form Penarikan
      |
      v
Pilih Nasabah → Tampil saldo aktif
      |
      v
Input nominal penarikan
      |
      v
Validasi: nominal <= saldo? --NO--> Tampil error
      |YES
      v
Konfirmasi & Simpan → Saldo BERKURANG → Log Transaksi
      |
      v
Cetak Bukti Penarikan
```

---

## 8. Keamanan

| Aspek | Implementasi |
|-------|-------------|
| Autentikasi | Laravel session-based auth + CSRF protection |
| Otorisasi | Middleware role + Laravel Policies |
| SQL Injection | Eloquent ORM (parameterized queries) |
| XSS | Blade `{{ }}` auto-escape |
| Mass Assignment | `$fillable` atau `$guarded` di setiap Model |
| Password | bcrypt hashing via `Hash::make()` |
| File Upload | Validasi MIME type + ukuran, simpan di storage private |
| Rate Limiting | Laravel throttle middleware pada auth routes |

---

## 9. UI/UX Guidelines

- **Responsive** — mobile-first dengan TailwindCSS
- **Sidebar navigation** — menu terpisah per role
- **Konfirmasi modal** — untuk aksi delete / void transaksi
- **Flash messages** — sukses/error/warning setelah aksi
- **Datatables** — tabel dengan search, sort, paginasi
- **Print-friendly** — struk transaksi dengan CSS `@media print`
- **Loading state** — spinner/skeleton saat kalkulasi & submit

---

## 10. Prioritas Pengembangan (Roadmap)

| Fase | Fitur | Estimasi |
|------|-------|----------|
| **Fase 1 — Core** | Setup Laravel, Auth, Dashboard, Nasabah, Jenis Sampah | Sprint 1 |
| **Fase 2 — Transaksi** | Setoran, Penarikan, Riwayat Transaksi | Sprint 2 |
| **Fase 3 — Laporan** | Laporan PDF & Excel, Statistik | Sprint 3 |
| **Fase 4 — Polish** | Panel Nasabah, Pengaturan, Notifikasi | Sprint 4 |

---

## 11. Kriteria Penerimaan (Acceptance Criteria)

- [ ] Admin dapat login dan mengakses semua fitur
- [ ] Petugas hanya dapat input transaksi dan lihat data nasabah
- [ ] Nasabah hanya dapat lihat saldo dan riwayat sendiri
- [ ] Setoran berhasil menambah saldo nasabah secara akurat
- [ ] Penarikan melebihi saldo ditolak dengan pesan error
- [ ] Semua transaksi tercatat dengan kode unik dan timestamped
- [ ] Laporan dapat diunduh sebagai PDF dan Excel
- [ ] Aplikasi berjalan responsif di mobile dan desktop

