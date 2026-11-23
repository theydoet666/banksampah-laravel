# PRD — Sistem Informasi Bank Sampah BAA

> **Product Requirements Document (PRD)**  
> Versi: 1.6.0  
> Tanggal: 23 November 2026  
> Referensi Proyek Lama: https://github.com/theydoet666/banksampah  
> Tech Stack: **Laravel 11 + MySQL + Blade + TailwindCSS + DaisyUI + Alpine.js + SweetAlert2**

---

## 1. Latar Belakang & Tujuan

Bank Sampah BAA adalah sistem pengelolaan sampah terpadu berbasis tabungan digital yang memungkinkan masyarakat (nasabah) menyetorkan sampah terpilah dan menukarnya menjadi saldo rupiah. Pada versi 1.6.0 ini, sistem telah disempurnakan mencakup:
1. **Multi-Unit Bank Sampah** di tingkat Dusun/RW.
2. **Role Berjenjang 4 Tingkat** (`admin`, `pengelola`, `petugas`, `nasabah`).
3. **Mekanisme Multi-Identifier Login & Auto Onboarding Nasabah** yang fleksibel dan ramah warga desa.
4. **Struktur Dua Harga Komoditas** (Harga Beli Nasabah & Harga Jual Pengepul) dengan kalkulasi margin keuntungan desa secara *real-time*.
5. **Ekstensi Satuan Ukur Komoditas** (`kg`, `gram`, `unit`, `buah`, `pcs`, `liter`).
6. **Manajemen Penjualan Sampah Gudang Induk ke Pengepul** serta laporan laba/rugi usaha desa terpadu.
7. **Pencatatan & Pelunasan Piutang Penjualan Pengepul** (penjualan tempo yang dapat dilunasi bertahap/tunai/transfer dengan konfirmasi interaktif SweetAlert2).
8. **Otorisasi PIN Transaksi Keuangan** (PIN 6-digit wajib untuk otorisasi pencairan saldo nasabah oleh Pengelola/Bendahara guna melindungi kas desa).
9. **Kop Surat & Logo Dinamis pada Seluruh Dokumen Cetak/PDF** (Base64 data embedding untuk DomPDF, Surat Jalan, dan Struk Kasir sesuai pengaturan identitas sistem).
10. **Portal Mandiri Nasabah Mobile-First** dengan Mobile App Bottom Navigation Bar untuk pengalaman pengguna layaknya aplikasi mobile banking.
11. **Penyempurnaan Rekapitulasi Laporan & Pelaporan Status Transaksi** (kalkulasi penarikan akurat dan badge status resmi).
12. **Saldo Awal Migrasi Pembukuan Manual** (fitur khusus Administrator untuk memasukkan saldo sisa pembukuan manual lama nasabah secara aman dan teraudit).
13. **Audit Log & Jejak Aktivitas Sistem** (fitur forensik dan akuntabilitas eksklusif Administrator untuk memantau semua aktivitas dari seluruh akun pengguna serta ekspor riwayat ke CSV).
14. **Penghapusan Aman Master Data (Safety Guard)** pada Unit Bank Sampah dan Jenis Sampah untuk mencegah hilangnya riwayat keuangan warga.
15. **Artisan Reset Tool** (`php artisan bank-sampah:reset-data`) untuk reset data transaksi pengujian secara higienis.

### Tujuan Utama
- Mendigitalisasi pencatatan setoran dan penarikan tabungan sampah di setiap pos dusun/RW.
- Mengakomodasi operasional berjenjang (Admin Desa, Pengelola/Bendahara Gudang Induk, Petugas Pos Dusun, dan Nasabah Warga).
- Menyediakan transparansi penetapan harga komoditas (harga beli dari warga vs harga jual ke industri/pengepul).
- Memberikan kemudahan akses login bagi warga desa (menggunakan No. Rekening, No. HP/WhatsApp, atau Email).
- Menyediakan pengawasan stok fisik sampah gudang desa, akuntabilitas laba/rugi operasional, dan proteksi kas via PIN.
- Menjamin identitas branding institusi desa terintegrasi pada seluruh nota, struk, dan laporan cetak.

---

## 2. Pemangku Kepentingan (Stakeholders & Roles)

| Role | Deskripsi & Batasan Akses |
|------|---------------------------|
| **Admin Desa / Administrator Sistem** (`admin`) | Administrator penuh tingkat desa — berwenang mengelola pembentukan unit dusun/RW, manajemen akun seluruh pengguna sistem (admin, pengelola, petugas), identitas branding/logo, dan konfigurasi master sistem. |
| **Pengelola Bank Sampah / Bendahara** (`pengelola`) | Koordinator operasional & bendahara Bank Sampah Induk — berwenang mengelola transaksi penjualan sampah ke pengepul, pelunasan piutang tempo, otorisasi/approval penarikan saldo nasabah dengan PIN otorisasi, monitoring stok fisik gudang desa, serta laporan laba/rugi operasional. *(Dibatasi: 403 Forbidden untuk manajemen user sistem, unit dusun, dan branding logo)*. |
| **Petugas Unit** (`petugas`) | Operator di pos dusun/RW — mencatat timbangan setoran sampah warga, melayani pengajuan tarik saldo ke pengelola desa, mendaftarkan nasabah unitnya, dan mencetak struk transaksi. |
| **Nasabah** (`nasabah`) | Warga desa pemilik buku tabungan digital — memantau saldo tabungan, mutasi kredit/debit secara mandiri, mengajukan penarikan saldo online, serta melihat katalog harga beli sampah melalui tampilan web/mobile responsif. |

---

## 3. Ruang Lingkup (Scope)

### Dalam Lingkup (In Scope)
- Autentikasi multi-identifier (Email / No. Rekening / No. HP / Username) & toggle show/hide password.
- Auto-account onboarding nasabah (password default 6 digit No. HP) & form registrasi mandiri warga online (`/register-nasabah`).
- Manajemen Unit Dusun/RW (Multi-Unit scoping).
- Master Kategori & Jenis Sampah dengan **Struktur Dua Harga** (Harga Beli & Harga Jual) serta dukungan satuan `kg`, `gram`, `unit`, `buah`, `pcs`, `liter`.
- Transaksi setoran sampah warga di pos unit (multi-item detail).
- Transaksi penarikan saldo dengan alur persetujuan (*approval workflow*) dan **verifikasi 6-Digit PIN Otorisasi** oleh Pengelola Desa.
- Monitoring stok gudang induk dan transaksi penjualan ke pengepul (dengan fitur auto-fill harga jual standar).
- Alur pelunasan piutang penjualan pengepul (pembayaran tempo) dengan konfirmasi SweetAlert2.
- Laporan Keuangan, Operasional, Stok Gudang, dan Laba/Rugi (PDF & Excel) dengan **Kop Surat & Logo Resmi Dinamis**.
- Portal layanan mandiri nasabah responsif mobile dengan **Bottom Navigation Bar**.
- Pengaturan identitas bank sampah, logo resmi, favicon kustom tab browser, dan profil desa.
- Tool pemeliharaan data: command artisan `bank-sampah:reset-data` untuk pengujian bersih dari nol.

### Di Luar Lingkup (Out of Scope)
- Integrasi payment gateway otomatis ke rekening bank umum (penarikan saldo diserahterimakan langsung atau via transfer manual kas desa).
- SMS/WhatsApp gateway pihak ketiga berbayar (fase berikutnya).
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
| NAS-02 | Tambah nasabah baru oleh Admin Desa dan Petugas Pos Unit (otomatis terkunci ke unit pos dusun petugas pendaftar) + auto-generate No. Rekening unik format `BS-YYYY-xxxxx` | P0 | Selesai |
| NAS-03 | Kartu info kredensial login mandiri & tombol Reset Password nasabah | P0 | Selesai |
| NAS-04 | Detail buku tabungan (saldo riil + riwayat transaksi debit/kredit) | P0 | Selesai |
| NAS-05 | Cetak kartu anggota nasabah (PDF) | P1 | Selesai |
| NAS-06 | **Saldo Awal Migrasi Pembukuan Manual**: Fitur khusus hak akses Administrator untuk mencatat sisa saldo nasabah dari sistem pembukuan manual lama (tersedia saat pendaftaran nasabah baru maupun penambahan saldo awal pada nasabah eksisting). Tercatat sebagai mutasi bertanda `Saldo Awal` di buku tabungan dan menambah saldo nasabah tanpa mempengaruhi timbangan fisik sampah gudang. Role Petugas dan Pengelola diblokir (403 Forbidden). | P0 | Selesai |

### 4.4 Manajemen Komoditas & Struktur Dua Harga
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| SAM-01 | Master jenis sampah dengan **Struktur Dua Harga**: Harga Beli (Nasabah) & Harga Jual (Pengepul) | P0 | Selesai |
| SAM-02 | Kalkulator margin keuntungan kas desa interaktif secara live di form tambah/edit | P0 | Selesai |
| SAM-03 | Pilihan satuan ukur luas: `kg`, `gram`, `unit`, `buah`, `pcs`, `liter` | P0 | Selesai |
| SAM-04 | Riwayat fluktuasi harga komoditas (`harga_sampah_log`) untuk harga beli dan harga jual | P1 | Selesai |
| SAM-05 | Manajemen Kategori Sampah (Plastik, Kertas, Logam, Kaca, Organik, Elektronik) | P0 | Selesai |
| SAM-06 | **Hapus Jenis Sampah dengan Proteksi Relasi Data**: Komoditas yang sudah pernah tercatat pada transaksi setoran nasabah atau penjualan ke pengepul dilarang dihapus demi menjaga integritas pembukuan; komoditas baru/tanpa transaksi dapat dihapus tuntas dengan modal konfirmasi | P0 | Selesai |

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
| TAR-02 | Validasi saldo cukup (tidak boleh minus) & pemisahan saldo riil vs saldo tertahan | P0 | Selesai |
| TAR-03 | Alur persetujuan (*approval workflow*) penarikan oleh Pengelola/Admin Desa | P0 | Selesai |
| TAR-04 | Pemotongan saldo nasabah saat penarikan disetujui (status: disetujui/ditolak) | P0 | Selesai |
| TAR-05 | Cetak bukti / struk penarikan saldo dengan logo dinamis | P1 | Selesai |
| TAR-06 | **Otorisasi PIN 6-Digit**: Pengelola/Admin wajib memasukkan PIN otorisasi transaksi yang valid saat menyetujui penarikan saldo guna melindungi integritas kas desa | P0 | Selesai |
| TAR-07 | **Manajemen PIN Pengguna**: Fitur pembuatan dan pembaruan PIN di menu profil (`/profile`) serta tautan pintasan (*quick shortcut*) di halaman persetujuan | P0 | Selesai |

### 4.7 Penjualan Sampah ke Pengepul & Gudang Induk
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| JUA-01 | Master data rekanan pengepul / industri off-taker daur ulang | P0 | Selesai |
| JUA-02 | Monitoring stok fisik sampah gudang desa siap angkut | P0 | Selesai |
| JUA-03 | Form penjualan sampah ke pengepul dengan **Auto-Fill Harga Jual Standar** dari master | P0 | Selesai |
| JUA-04 | Validasi kuantitas jual tidak melebihi stok gudang yang tersedia | P0 | Selesai |
| JUA-05 | Pengurangan stok gudang otomatis saat transaksi penjualan disimpan | P0 | Selesai |
| JUA-06 | Laporan Laba/Rugi Desa: Pendapatan Jual vs Beban Setoran Nasabah | P0 | Selesai |
| JUA-07 | **Pencatatan Status Pembayaran Penjualan**: Mendukung status pembayaran langsung (`lunas`) atau sistem tempo/piutang (`piutang`) | P0 | Selesai |
| JUA-08 | **Alur Pelunasan Piutang Pengepul**: Modal pelunasan piutang interaktif dengan pencatatan tanggal, metode bayar (transfer/tunai), catatan referensi, dan penerima kas, dilengkapi dialog konfirmasi **SweetAlert2** | P0 | Selesai |
| JUA-09 | **Cetak Surat Jalan & Nota Penjualan**: Dokumen cetak otomatis memuat rincian pelunasan dan logo resmi bank sampah | P1 | Selesai |

### 4.8 Laporan & Rekapitulasi
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| LAP-01 | Laporan transaksi setor/tarik (filter periode, jenis, unit dusun, status lengkap) | P0 | Selesai |
| LAP-02 | Laporan laba/rugi penjualan sampah ke pengepul (filter tanggal) | P0 | Selesai |
| LAP-03 | Laporan valuasi stok gudang (modal beli vs potensi jual) | P0 | Selesai |
| LAP-04 | Ekspor laporan format PDF (DomPDF) dan Excel (Maatwebsite) | P1 | Selesai |
| LAP-05 | **Kop Surat & Logo Dinamis Resmi**: Seluruh dokumen cetak/PDF otomatis memuat logo institusi (Base64) serta identitas alamat dan nomor kontak yang sinkron dengan pengaturan sistem | P0 | Selesai |
| LAP-06 | **Penyempurnaan Rekapitulasi Kas & Status Badge**: Perhitungan kartu rekapitulasi nilai penarikan akurat menghitung status `disetujui` dan visualisasi badge status profesional tanpa coret salah | P0 | Selesai |

### 4.9 Manajemen Unit Bank Sampah (Dusun / RW)
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| UNT-01 | Master unit bank sampah (Dusun / RW) dengan kode unit unik, nama ketua, nomor kontak, dan alamat | P0 | Selesai |
| UNT-02 | Tambah, edit, dan detail unit beserta visualisasi rekapitulasi nasabah dan transaksi | P0 | Selesai |
| UNT-03 | Toggle aktif/nonaktif status operasional unit bank sampah | P0 | Selesai |
| UNT-04 | **Hapus Unit Bank Sampah dengan Proteksi Relasi Data**: Unit yang memiliki relasi data dicegah dari penghapusan secara ketat demi keamanan saldo dan audit warga desa; unit kosong dapat dihapus tuntas. | P0 | Selesai |

### 4.10 Portal Mandiri Nasabah (Mobile-First Experience)
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| NAS-06 | **Mobile App Bottom Navigation Bar**: Bilah navigasi bawah mengambang (*sticky bottom bar*) layaknya aplikasi mobile banking (Beranda, Riwayat, Tarik Saldo, Profil) | P0 | Selesai |
| NAS-07 | **Antarmuka Mobile Adaptif**: Kartu ATM virtual digital tabungan, kartu ringkasan bulanan, dan tabel riwayat transaksi mutasi yang proporsional pada semua ukuran layar ponsel (360px - 412px) | P0 | Selesai |

### 4.11 Audit Log & Rekam Jejak Sistem (Khusus Administrator)
| ID | Fitur | Prioritas | Status |
|----|-------|-----------|--------|
| AUD-01 | **Pusat Pemantauan Log Terpadu**: Halaman dashboard audit log (`/admin/audit-log`) untuk memantau kronologi semua aktivitas pengguna (Admin, Pengelola, Petugas, Nasabah) secara *real-time* | P0 | Selesai |
| AUD-02 | **Pencatatan Otomatis Seluruh Modul**: Merekam event login, logout, failed login, transaksi setor, tarik, otorisasi PIN, penolakan, saldo awal, penjualan pengepul, pelunasan piutang, reset password, dan konfigurasi master data | P0 | Selesai |
| AUD-03 | **Penyaringan & Pencarian Lengkap**: Filter pencarian multivariat (kata kunci deskripsi/pengguna/IP, akun pengguna, role, modul sistem, jenis aksi, dan rentang tanggal) | P0 | Selesai |
| AUD-04 | **Modal Rincian & Forensik Data**: Tampilan detail aktivitas lengkap beserta snapshot perbandingan data lama vs data baru (*old_values* vs *new_values*), alamat IP, dan perangkat (*user agent*) | P0 | Selesai |
| AUD-05 | **Ekspor Audit Log (CSV)**: Unduhan rekapitulasi audit log terfilter ke file format CSV (UTF-8 BOM Excel-friendly) untuk keperluan pelaporan resmi desa, BPD, maupun inspektorat | P0 | Selesai |
| AUD-06 | **Proteksi Ketat Hak Akses**: Akses eksklusif hanya untuk role `admin`; upaya akses oleh role `pengelola`, `petugas`, dan `nasabah` otomatis diblokir (403 Forbidden) | P0 | Selesai |

---

## 5. Desain Database & Skema Terkini

### ERD Overview (v1.6.0)
```
audit_logs (Jejak forensik seluruh aktivitas sistem)
  └── N:1 ── users (nullable on delete)

units (Dusun/RW)
  ├── 1:N ── users (admin, pengelola [PIN], petugas, nasabah)
  ├── 1:N ── nasabah
  └── 1:N ── transaksi (setor & tarik warga)
                  │
                  ├── detail_transaksi ── jenis_sampah ── kategori_sampah
                                               │               │
pengepul (Off-taker)                           │       harga_sampah_log
  │                                            │
  └── 1:N ── penjualan_sampah                  │
                  │ (Lunas / Piutang + Pelunasan)
                  └── detail_penjualan_sampah ─┘ (stok keluar)
```

### Rincian Tabel Utama

#### `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| unit_id | BIGINT NULL FK → units | Lokasi penugasan petugas unit |
| name | VARCHAR(100) | |
| email | VARCHAR(100) UNIQUE | |
| password | VARCHAR(255) | Hash bcrypt |
| pin | VARCHAR(255) NULL | Hash bcrypt PIN 6-digit untuk otorisasi transaksi kas |
| role | VARCHAR(30) | 'admin', 'pengelola', 'petugas', 'nasabah' |
| is_active | BOOLEAN DEFAULT 1 | |

#### `penjualan_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | BIGINT UNSIGNED AI PK | |
| kode_penjualan | VARCHAR(30) UNIQUE | Format `PJL-YYYYMMDD-xxxxx` |
| pengepul_id | BIGINT FK → pengepul | Mitra pembeli off-taker |
| pengelola_id | BIGINT FK → users | Pengelola yang mencatat penjualan awal |
| tanggal_penjualan | DATE | Tanggal pengiriman sampah |
| total_berat | DECIMAL(10,3) | Total kg terangkut |
| total_pendapatan | DECIMAL(15,2) | Total nilai penjualan |
| status_pembayaran | ENUM('lunas', 'piutang') DEFAULT 'lunas' | Status pelunasan dana |
| tanggal_pelunasan | DATE NULL | Tanggal penerimaan uang pelunasan |
| metode_pelunasan | VARCHAR(50) NULL | 'transfer' / 'tunai' |
| catatan_pelunasan | TEXT NULL | No. referensi transfer / bukti bayar |
| diterima_oleh | BIGINT NULL FK → users | Pengelola yang menerima & mencatat pelunasan |
| catatan | TEXT NULL | Catatan pengiriman |
| created_at / updated_at | TIMESTAMP | |

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
| status | ENUM('aktif','void','menunggu_persetujuan','disetujui','ditolak') | Status transaksi / persetujuan tarik |
| disetujui_oleh | BIGINT NULL FK → users | Pengelola/Admin yang meng-approve penarikan (dengan PIN) |

---

## 6. Fitur Pemeliharaan & Testing

- **Perintah Reset Data Transaksi**:
  ```bash
  php artisan bank-sampah:reset-data
  ```
  Menghapus seluruh transaksi setor, tarik, penjualan pengepul, dan nasabah dummy, mengembalikan auto-increment ke nomor 1, serta mempertahankan akun master, unit dusun, kategori/jenis sampah, dan konfigurasi logo desa.
- **Automated Feature Tests**:
  Seluruh alur logika bisnis dilindungi oleh **47 skenario pengujian otomatis (276 assertions)** di `tests/Feature/BankSampahTest.php` berbasis `DatabaseTransactions` dengan tingkat kelulusan 100%.

---

## 7. Status Pengujian & Kesiapan Produksi

- [x] Admin dapat login dan mengelola seluruh konfigurasi desa.
- [x] Pengelola dapat mengontrol stok gudang, menjual sampah ke pengepul, dan mencatat pelunasan piutang tempo (SweetAlert2).
- [x] Pengelola wajib menggunakan PIN otorisasi 6-digit untuk mencairkan saldo tabungan nasabah.
- [x] Petugas unit terisolasi dalam lingkup unit dusunnya masing-masing.
- [x] Nasabah dapat login mandiri via No. Rekening / No. HP dengan password aman.
- [x] Portal nasabah mobile-friendly dengan Mobile App Bottom Navigation Bar.
- [x] Master komoditas sampah mendukung harga beli nasabah, harga jual pengepul, satuan fleksibel, serta penghapusan aman berproteksi relasi transaksi.
- [x] Master unit bank sampah mendukung penambahan, pengeditan, penonaktifan, serta penghapusan aman dengan perlindungan relasi data.
- [x] Laporan laba/rugi otomatis mengkalkulasi selisih penjualan pengepul dikurangi beban setoran nasabah.
- [x] Seluruh dokumen cetak dan PDF (Laba/Rugi, Transaksi, Surat Jalan, Struk) otomatis memuat Logo resmi dinamis dan identitas sistem.
- [x] Pengaturan identitas mendukung upload logo resmi dan favicon kustom browser dengan simulasi mockup interaktif.
- [x] Antarmuka login profesional, elegan, responsif, dan dilengkapi toggle lihat kata sandi.
