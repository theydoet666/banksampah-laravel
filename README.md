# Sistem Informasi Manajemen Bank Sampah BAA

Aplikasi web modern berbasis **Laravel 11**, **MySQL**, **Tailwind CSS**, dan **Alpine.js** yang dirancang untuk mengelola operasional bank sampah secara transparan, akuntabel, dan terintegrasi dari pencatatan setoran, penarikan saldo, histori transaksi, buku tabungan nasabah digital, hingga ekspor laporan akuntansi (PDF & Excel).

---

## 🚀 Fitur Utama

### 1. Multi-Role Authentication & Access Control
- **Administrator**: Memiliki hak akses menyeluruh ke seluruh modul (Master Data, Pengaturan Sistem, Manajemen Pengguna, Transaksi, Laporan, dan Void).
- **Petugas (Operator)**: Mengoperasikan transaksi harian penimbangan (setor sampah) dan pencairan saldo nasabah (tarik saldo), pendaftaran nasabah, dan pencetakan struk.
- **Nasabah**: Portal mandiri (*self-service*) untuk memantau saldo tabungan, mutasi kredit/debit, dan unduh riwayat secara *real-time*.

### 2. Modul Master Data Sampah
- Pengelompokan kategori sampah (Organik, Anorganik, B3, Residu, dll).
- Manajemen jenis sampah dengan satuan (kg, liter, pcs) dan harga per satuan dinamis.
- Pencatatan riwayat audit perubahan harga (*price history log*).

### 3. Modul Manajemen Nasabah
- Pembuatan nomor rekening otomatis dengan format `BS-YYYY-XXXXX`.
- Perekaman biodata lengkap: NIK (16 digit), No HP, alamat domisili, jenis kelamin, tanggal lahir, dan foto profil.
- Dukungan *Soft Deletes* dan fitur aktivasi/nonaktifasi status rekening.

### 4. Transaksi & Kalkulasi Saldo
- **Setor Sampah (Multi-Item)**: Input dinamis berbagai jenis sampah sekaligus dengan kalkulasi otomatis subtotal dan bobot total (*real-time* via Alpine.js).
- **Snapshot Harga**: Setiap detail transaksi mengunci harga satuan saat transaksi terjadi sehingga laporan historis tetap akurat meski harga master sampah berubah.
- **Tarik Saldo**: Proteksi saldo minimum & saldo tidak mencukupi melalui `InsufficientBalanceException`.
- **Cetak Struk Transaksi**: Cetak nota transaksi termal / A4 dengan format resmi.
- **Fitur Void Transaksi**: Pembatalan transaksi salah input oleh Administrator dengan pengembalian (*rollback*) saldo otomatis ke rekening nasabah.

### 5. Pelaporan & Export
- Filter transaksi berdasarkan rentang tanggal, jenis transaksi, dan status.
- Ekspor laporan berformat **PDF** (Barryvdh DomPDF) siap cetak.
- Ekspor rekapitulasi data berformat **Microsoft Excel** (Maatwebsite Excel).

### 6. Desain Responsif & UX Modern
- Desain antarmuka bersih menggunakan **Tailwind CSS** dan komponen **DaisyUI**.
- Interaktivitas dinamis tanpa reload halaman penuh menggunakan **Alpine.js**.
- Konfirmasi aksi sensitif via Modal Konfirmasi Global reusable.
- Indikator loading interaktif saat submit formulir.

---

## 🛠️ Tech Stack

- **Backend Framework**: Laravel 11.x
- **Bahasa Pemrograman**: PHP 8.2+
- **Database**: MySQL 8.x / MariaDB
- **Frontend Stack**: Vite, Tailwind CSS v3, DaisyUI v4, Alpine.js, Chart.js
- **Libraries**:
  - `barryvdh/laravel-dompdf` (PDF Generation)
  - `maatwebsite/excel` (Excel Spreadsheet Export)
  - `fakerphp/faker` (Data Dummy Generator)

---

## 📋 Akun Default & Demo

Semua akun menggunakan password standar: `password`

| Role | Email | Password | Akses Dashboard |
|------|-------|----------|-----------------|
| **Admin** | `admin@banksampah.test` | `password` | `/admin/dashboard` |
| **Petugas** | `petugas@banksampah.test` | `password` | `/petugas/dashboard` |
| **Nasabah** | `nasabah@banksampah.test` | `password` | `/nasabah/dashboard` |

> *Catatan: Terdapat 25 nasabah demo realistis dan 65 transaksi historis (50 setor, 15 tarik, 2 void) yang digenerate oleh `DemoSeeder`.*

---

## 💻 Panduan Instalasi & Menjalankan

### 1. Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `gd`)
- Composer
- Node.js & NPM
- MySQL Server (Laragon, XAMPP, atau Docker)

### 2. Kloning & Pengaturan Lingkungan
```bash
# Clone repositori
git clone https://github.com/theydoet666/banksampah.git Bank-Sampah-BAA
cd Bank-Sampah-BAA

# Salin file konfigurasi environment
cp .env.example .env

# Generate APP_KEY
php artisan key:generate
```

### 3. Konfigurasi Database
Sesuaikan parameter database di file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_sampah_baa
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Instal Dependensi & Migrasi
```bash
# Install package PHP
composer install

# Install dependensi frontend
npm install

# Buat symbolic link ke folder storage
php artisan storage:link

# Jalankan migrasi dan seeding database lengkap
php artisan migrate:fresh --seed
```

### 5. Kompilasi Aset & Jalankan Server
```bash
# Build frontend assets
npm run build

# Jalankan development server
php artisan serve
```
Akses aplikasi melalui browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Automated Tests

Aplikasi dilengkapi dengan test suite otomatis (Feature & Unit Tests) yang menguji otentikasi peran, pembatasan hak akses, pembuatan nomor rekening, penambahan/pengurangan saldo, pencegahan saldo minus, void transaksi, serta ekspor PDF/Excel:

```bash
php artisan test --filter=BankSampahTest
```

---

## 🗄️ Skema Database

```
users
  ├── id, name, email, password, role ('admin'|'petugas'|'nasabah'), is_active
nasabah
  ├── id, user_id (FK), no_rekening, nama, nik, saldo, status ('aktif'|'nonaktif'), registered_by (FK)
kategori_sampah
  ├── id, nama, deskripsi
jenis_sampah
  ├── id, kategori_id (FK), nama, satuan, harga_per_satuan, is_active
harga_sampah_log
  ├── id, jenis_sampah_id (FK), harga_lama, harga_baru, diubah_oleh (FK)
transaksi
  ├── id, kode_transaksi, nasabah_id (FK), jenis ('setor'|'tarik'), total_berat, total_nilai, status ('aktif'|'void'), petugas_id (FK), tanggal_transaksi
detail_transaksi
  ├── id, transaksi_id (FK), jenis_sampah_id (FK), berat, harga_satuan (snapshot), subtotal
app_settings
  ├── id, key, value, label, group
```

---

## 📄 Lisensi
Hak Cipta Dilindungi &copy; 2026 **Bank Sampah BAA**. Dikembangkan untuk tata kelola sampah yang ramah lingkungan dan bernilai ekonomis.
