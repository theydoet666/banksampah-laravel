# PRD — Sistem Informasi Bank Sampah BAA

> **Product Requirements Document (PRD)**  
> Versi: 1.2.0  
> Tanggal: 12 November 2026  
> Referensi Proyek Lama: https://github.com/theydoet666/banksampah  
> Tech Stack: **Laravel 11 + MySQL + Blade + TailwindCSS + DaisyUI + Alpine.js**

---

## 1. Latar Belakang & Tujuan

Bank Sampah BAA adalah sistem pengelolaan sampah terpadu berbasis tabungan digital yang memungkinkan masyarakat (nasabah) menyetorkan sampah terpilah dan menukarnya menjadi saldo rupiah. Pada versi 1.2.0 ini, sistem telah diperluas mencakup:
1. **Multi-Unit Bank Sampah** di tingkat Dusun/RW.
2. **Role Berjenjang 4 Tingkat** (`admin`, `pengelola`, `petugas`, `nasabah`).
3. **Mekanisme Multi-Identifier Login & Auto Onboarding Nasabah** yang fleksibel dan ramah warga desa.
4. **Struktur Dua Harga Komoditas** (Harga Beli Nasabah & Harga Jual Pengepul) dengan kalkulasi margin keuntungan desa secara *real-time*.
5. **Manajemen Penjualan Sampah Gudang Induk ke Pengepul** serta laporan laba/rugi usaha desa terpadu.

### Tujuan Utama
- Mendigitalisasi pencatatan setoran dan penarikan tabungan sampah di setiap pos dusun/RW.
- Mengakomodasi operasional berjenjang (Admin Desa, Pengelola/Bendahara Gudang Induk, Petugas Pos Dusun, dan Nasabah Warga).
- Menyediakan transparansi penetapan harga komoditas (harga beli dari warga vs harga jual ke industri/pengepul).
- Memberikan kemudahan akses login bagi warga desa (menggunakan No. Rekening, No. HP/WhatsApp, atau Email).
- Menyediakan pengawasan stok fisik sampah gudang desa dan akuntabilitas laba/rugi operasional.

---

## 2. Pemangku Kepentingan (Stakeholders & Roles)

| Role | Deskripsi & Batasan Akses |
|------|---------------------------|
| **Admin Desa / Administrator Sistem** (`admin`) | Administrator penuh tingkat desa — berwenang mengelola pembentukan unit dusun/RW, manajemen akun seluruh pengguna sistem (admin, pengelola, petugas), identitas branding/logo, dan konfigurasi master sistem. |
| **Pengelola Bank Sampah / Bendahara** (`pengelola`) | Koordinator operasional & bendahara Bank Sampah Induk — berwenang mengelola transaksi penjualan sampah ke pengepul, otorisasi/approval penarikan saldo nasabah, monitoring stok fisik gudang desa, serta laporan laba/rugi operasional. *(Dibatasi: 403 Forbidden untuk manajemen user sistem, unit dusun, dan branding logo)*. |
| **Petugas Unit** (`petugas`) | Operator di pos dusun/RW — mencatat timbangan setoran sampah warga, melayani pengajuan tarik saldo ke pengelola desa, mendaftarkan nasabah unitnya, dan mencetak struk transaksi. |
| **Nasabah** (`nasabah`) | Warga desa pemilik buku tabungan digital — memantau saldo tabungan, mutasi kredit/debit secara mandiri, mengajukan penarikan saldo online, serta melihat katalog harga beli sampah. |

---

## 3. Ruang Lingkup (Scope)

### Dalam Lingkup (In Scope)
- Autentikasi multi-identifier (Email / No. Rekening / No. HP / Username) & toggle show/hide password.
- Auto-account onboarding nasabah (password default 6 digit No. HP) & form registrasi mandiri warga online (`/register-nasabah`).
- Manajemen Unit Dusun/RW (Multi-Unit scoping).
- Master Kategori & Jenis Sampah dengan **Struktur Dua Harga** (Harga Beli & Harga Jual) serta dukungan satuan `kg`, `gram`, `unit`, `buah`, `pcs`, `liter`.
- Transaksi setoran sampah warga di pos unit (multi-item detail).
- Transaksi penarikan saldo dengan alur persetujuan (*approval workflow*) oleh Pengelola Desa.
- Monitoring stok gudang induk dan transaksi penjualan ke pengepul (dengan fitur auto-fill harga jual standar).
- Laporan Keuangan, Operasional, Stok Gudang, dan Laba/Rugi (PDF & Excel).
- Pengaturan identitas bank sampah, logo, dan profil desa.
- Tool pemeliharaan data: command artisan `bank-sampah:reset-data` untuk pengujian bersih dari nol.

### Di Luar Lingkup (Out of Scope)
- Integrasi transfer host-to-host bank otomatis (penarikan saldo tetap diserahterimakan tunai di pos loket/kantor).
- Aplikasi native Android/iOS khusus (sistem sepenuhnya responsif web mobile PWA-ready).
- SMS/WhatsApp gateway pihak ketiga berbayar (fase berikutnya).

---

## 4. Fitur & Modul

### 4.1 Autentikasi & Onboarding Warga
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| AUTH-01 | Login multi-identifier (Email, No. Rekening `BS-YYYY-xxxxx`, No. HP/WhatsApp, atau Shorthand Username `admin`/`pengelola`/`petugas`) | P0 | Selesai |
| AUTH-02 | Fitur intip kata sandi (Show/Hide Password toggle dengan ikon mata) | P0 | Selesai |
| AUTH-03 | Desain Form Login modern split-screen 50:50 (Fintech style & responsif) | P0 | Selesai |
| AUTH-04 | Auto user onboarding: akun login otomatis terbit saat nasabah didaftarkan petugas pos (password default 6 digit No. HP) | P0 | Selesai |
| AUTH-05 | Registrasi mandiri nasabah online melalui portal publik (`/register-nasabah`) | P0 | Selesai |
| AUTH-06 | Reset password nasabah ke default oleh Admin/Petugas di menu profil nasabah | P0 | Selesai |
| AUTH-07 | Role-based access control (Admin, Pengelola, Petugas, Nasabah) | P0 | Selesai |

### 4.2 Dashboard Analitik
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| DASH-01 | Ringkasan nasabah aktif, total sampah masuk (kg), dan saldo tabungan | P0 | Selesai |
| DASH-02 | Grafik statistik setoran 7 hari terakhir (Chart.js) | P1 | Selesai |
| DASH-03 | Scoping dashboard khusus per role (Admin, Pengelola, Petugas, Nasabah) | P0 | Selesai |

### 4.3 Manajemen Nasabah
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| NAS-01 | Daftar nasabah (searchable, filter per unit dusun, paginated) | P0 | Selesai |
| NAS-02 | Tambah nasabah baru + auto-generate No. Rekening unik format `BS-YYYY-xxxxx` | P0 | Selesai |
| NAS-03 | Kartu info kredensial login mandiri & tombol Reset Password nasabah | P0 | Selesai |
| NAS-04 | Detail buku tabungan (saldo riil + riwayat transaksi debit/kredit) | P0 | Selesai |
| NAS-05 | Cetak kartu anggota nasabah (PDF) | P1 | Selesai |

### 4.4 Manajemen Komoditas & Struktur Dua Harga
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| SAM-01 | Master jenis sampah dengan **Struktur Dua Harga**: Harga Beli (Nasabah) & Harga Jual (Pengepul) | P0 | Selesai |
| SAM-02 | Kalkulator margin keuntungan kas desa interaktif secara live di form tambah/edit | P0 | Selesai |
| SAM-03 | Pilihan satuan ukur luas: `kg`, `gram`, `unit`, `buah`, `pcs`, `liter` | P0 | Selesai |
| SAM-04 | Riwayat fluktuasi harga komoditas (`harga_sampah_log`) untuk harga beli dan harga jual | P1 | Selesai |
| SAM-05 | Manajemen Kategori Sampah (Plastik, Kertas, Logam, Kaca, Organik, Elektronik) | P0 | Selesai |

### 4.5 Transaksi Setoran Sampah (Pos Unit Dusun)
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| SET-01 | Form penimbangan setoran (pilih nasabah, multi-baris item sampah) | P0 | Selesai |
| SET-02 | Auto-kalkulasi nilai rupiah setoran (berat x harga beli nasabah) | P0 | Selesai |
| SET-03 | Update otomatis saldo tabungan nasabah secara instan | P0 | Selesai |
| SET-04 | Cetak struk transaksi setoran (print-friendly) | P1 | Selesai |
| SET-05 | Pembatalan / void transaksi setoran oleh Admin (saldo ditarik kembali) | P1 | Selesai |

### 4.6 Transaksi Penarikan Saldo & Alur Persetujuan
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| TAR-01 | Pengajuan penarikan saldo oleh nasabah atau petugas pos unit | P0 | Selesai |
| TAR-02 | Validasi saldo cukup (tidak boleh minus) | P0 | Selesai |
| TAR-03 | Alur persetujuan (*approval workflow*) penarikan oleh Pengelola/Admin Desa | P0 | Selesai |
| TAR-04 | Pemotongan saldo nasabah saat penarikan disetujui (status: disetujui/ditolak) | P0 | Selesai |
| TAR-05 | Cetak bukti penarikan saldo | P1 | Selesai |

### 4.7 Penjualan Sampah ke Pengepul & Gudang Induk
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| JUA-01 | Master data rekanan pengepul / industri off-taker daur ulang | P0 | Selesai |
| JUA-02 | Monitoring stok fisik sampah gudang desa siap angkut | P0 | Selesai |
| JUA-03 | Form penjualan sampah ke pengepul dengan **Auto-Fill Harga Jual Standar** dari master | P0 | Selesai |
| JUA-04 | Validasi kuantitas jual tidak melebihi stok gudang yang tersedia | P0 | Selesai |
| JUA-05 | Pengurangan stok gudang otomatis saat transaksi penjualan disimpan | P0 | Selesai |
| JUA-06 | Laporan Laba/Rugi Desa: Pendapatan Jual vs Beban Setoran Nasabah | P0 | Selesai |

### 4.8 Laporan & Rekapitulasi
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| LAP-01 | Laporan transaksi setor/tarik (filter periode, jenis, unit dusun) | P0 | Selesai |
| LAP-02 | Laporan laba/rugi penjualan sampah ke pengepul (filter tanggal) | P0 | Selesai |
| LAP-03 | Laporan valuasi stok gudang (modal beli vs potensi jual) | P0 | Selesai |
| LAP-04 | Ekspor laporan format PDF (DomPDF) dan Excel (Maatwebsite) | P1 | Selesai |

---

## 5. Desain Database & Skema Terkini

### ERD Overview (v1.2.0)
```
units (Dusun/RW)
  ├── 1:N ── users (admin, pengelola, petugas, nasabah)
  ├── 1:N ── nasabah
  └── 1:N ── transaksi (setor & tarik warga)
                  │
                  ├── detail_transaksi ── jenis_sampah ── kategori_sampah
                                               │               │
pengepul (Off-taker)                           │       harga_sampah_log
  │                                            │
  └── 1:N ── penjualan_sampah                  │
                  │                            │
                  └── detail_penjualan_sampah ─┘ (stok keluar)
```

### Rincian Tabel Utama

#### `jenis_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| kategori_id | BIGINT FK → kategori_sampah | |
| nama | VARCHAR(100) | Misal: Botol PET Bening, Kardus Box |
| satuan | VARCHAR(20) DEFAULT 'kg' | Mendukung: kg, gram, unit, buah, pcs, liter |
| harga_beli | DECIMAL(10,2) | Harga tebus setoran nasabah |
| harga_jual | DECIMAL(10,2) | Harga jual standar ke pengepul |
| harga_per_satuan | DECIMAL(10,2) | Alias backward compatibility (mengacu ke harga_beli) |
| deskripsi | TEXT NULL | |
| is_active | BOOLEAN DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

#### `harga_sampah_log`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| jenis_sampah_id | BIGINT FK → jenis_sampah | |
| tipe_perubahan | VARCHAR(20) | 'beli', 'jual', 'keduanya' |
| harga_beli_lama / harga_beli_baru | DECIMAL(10,2) NULL | Riwayat harga beli |
| harga_jual_lama / harga_jual_baru | DECIMAL(10,2) NULL | Riwayat harga jual |
| changed_by | BIGINT FK → users | Pengguna yang mengubah harga |
| created_at | TIMESTAMP | |

#### `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| unit_id | BIGINT NULL FK → units | Lokasi penugasan petugas unit |
| name | VARCHAR(100) | |
| email | VARCHAR(100) UNIQUE | |
| password | VARCHAR(255) | Hash bcrypt |
| role | VARCHAR(30) | 'admin', 'pengelola', 'petugas', 'nasabah' |
| is_active | BOOLEAN DEFAULT 1 | |

#### `nasabah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| user_id | BIGINT NULL FK → users | Akun pengguna untuk login mandiri |
| unit_id | BIGINT FK → units | Dusun domisili nasabah |
| no_rekening | VARCHAR(20) UNIQUE | Format `BS-YYYY-xxxxx` |
| nama | VARCHAR(100) | Nama lengkap warga |
| no_hp | VARCHAR(20) | Nomor WhatsApp/telepon |
| saldo | DECIMAL(15,2) DEFAULT 0 | Saldo riil tabungan sampah |
| registered_by | BIGINT NULL FK → users | Petugas pendaftar (NULL jika daftar mandiri online) |
| status | ENUM('aktif','nonaktif') | |

#### `transaksi`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| kode_transaksi | VARCHAR(30) UNIQUE | Format `TRX-YYYYMMDD-xxxxx` |
| unit_id | BIGINT FK → units | Lokasi unit transaksi |
| nasabah_id | BIGINT FK → nasabah | |
| jenis | ENUM('setor','tarik') | |
| total_berat | DECIMAL(10,3) NULL | Khusus transaksi setoran |
| total_nilai | DECIMAL(15,2) | Total nilai rupiah |
| status | ENUM('aktif','void','pending','disetujui','ditolak') | Status persetujuan tarik saldo |
| disetujui_oleh | BIGINT NULL FK → users | Pengelola/Admin yang meng-approve penarikan |

---

## 6. Fitur Pemeliharaan & Testing

- **Perintah Reset Data Transaksi**:
  ```bash
  php artisan bank-sampah:reset-data
  ```
  Menghapus seluruh transaksi setor, tarik, penjualan pengepul, dan nasabah dummy, mengembalikan auto-increment ke nomor 1, serta mempertahankan akun master, unit dusun, kategori/jenis sampah, dan konfigurasi logo desa.
- **Automated Feature Tests**:
  Seluruh alur logika bisnis dilindungi oleh 41 skenario pengujian otomatis di `tests/Feature/BankSampahTest.php` berbasis `DatabaseTransactions` tanpa mengganggu database development.

---

## 7. Status Pengujian & Kesiapan Produksi

- [x] Admin dapat login dan mengelola seluruh konfigurasi desa.
- [x] Pengelola dapat mengontrol stok gudang, menjual sampah ke pengepul, dan menyetujui penarikan saldo nasabah.
- [x] Petugas unit terisolasi dalam lingkup unit dusunnya masing-masing.
- [x] Nasabah dapat login mandiri via No. Rekening / No. HP dengan password aman.
- [x] Master komoditas sampah mendukung harga beli nasabah, harga jual pengepul, dan satuan `unit` / `buah`.
- [x] Laporan laba/rugi otomatis mengkalkulasi selisih penjualan pengepul dikurangi beban setoran nasabah.
- [x] Antarmuka login profesional, elegan, responsif, dan dilengkapi toggle lihat kata sandi.
