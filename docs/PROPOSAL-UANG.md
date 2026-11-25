# Proposal Arsitektur: Migrasi Tipe Data Kolom Uang ke DECIMAL(15,2) / Integer + BCMath

## 1. Latar Belakang & Masalah
Saat ini aplikasi Bank Sampah menyimpan nilai nominal keuangan (seperti `saldo`, `saldo_awal`, `total_nilai`, `subtotal`, `total_pendapatan`) serta bobot timbangan (`berat`, `total_berat`) menggunakan tipe data numerik standard MySQL (`DECIMAL(12,2)` pada beberapa tabel dan kalkulasi floating-point `(float)` di level PHP runtime).

Meskipun saat ini kalkulasi subtotal dan total telah diamankan menggunakan pembulatan `round($value, 2)`, penggunaan floating-point bawaan arsitektur IEEE 754 pada bahasa pemrograman memiliki risiko inheren:
- **Floating-point Imprecision**: Operasi aritmatika pecahan seperti `0.1 + 0.2` menghasilkan `0.30000000000000004`.
- **Akumulasi Selisih Pembulatan (Rounding Drift)**: Dalam jutaan baris transaksi dan agregasi buku besar, selisih 1 sen / 1 rupiah dapat terakumulasi menjadi selisih audit pembukuan keuangan desa.

---

## 2. Opsi Solusi Desain

### Opsi A: Migrasi Database ke `DECIMAL(15,2)` Murni + Ekstensi PHP `BCMath` (Direkomendasikan)
- **Database Skema**: Seluruh kolom uang dipastikan bertipe `DECIMAL(15, 2)` (mendukung hingga Rp 9.999.999.999.999,99 / ~9 triliun rupiah).
- **PHP Layer**: Menggunakan fungsi `bcadd()`, `bcsub()`, `bcmul()`, `bcdiv()` dengan skala presisi tetap (2 digit pecahan).
- **Kelebihan**:
  - Merepresentasikan nilai uang riil secara langsung di query SQL dan laporan Excel/PDF tanpa konversi unit.
  - Kompatibilitas tinggi dengan fungsi agregat SQL (`SUM()`, `AVG()`).
- **Risiko & Kompleksitas**:
  - Memerlukan ekstensi PHP `ext-bcmath` aktif di server production.
  - Nilai harus selalu dioperasikan sebagai string arbitrer di PHP, bukan casting `(float)`.

### Opsi B: Pattern Uang dalam Satuan Terkecil (Integer Cents / Sub-unit)
- **Database Skema**: Kolom uang disimpan sebagai integer satuan sen/rupiah penuh (`BIGINT`). Misal Rp 10.000 disimpan sebagai `1000000` (jika basis sen) atau `10000` (jika rupiah tanpa desimal).
- **PHP Layer**: Menggunakan library seperti `brick/money` atau `moneyphp/money`.
- **Kelebihan**:
  - Operasi aritmatika integer dijamin 100% eksak oleh CPU tanpa dependensi ekstensi khusus.
- **Risiko & Kompleksitas**:
  - Memerlukan refactoring masif pada seluruh export laporan, tampilan blade format rupiah, dan snapshot database lama.
  - Query langsung dari DB viewer memerlukan pembagian 100 untuk dibaca manusia.

---

## 3. Rencana Migrasi Terstruktur (Roadmap)
1. **Fase 1 (Audit Skema Eksisting)**: Verifikasi seluruh tabel (`nasabah`, `transaksi`, `detail_transaksi`, `penjualan_sampah`, `detail_penjualan_sampah`) telah menggunakan `DECIMAL(15,2)`.
2. **Fase 2 (Value Object / Math Helper)**: Buat class helper `App\Support\MoneyMath` yang membungkus fungsi `bcadd`, `bcsub`, `bcmul`, `bcdiv` dengan fallback aman.
3. **Fase 3 (Testing Regression)**: Buat unit tests kalkulasi keuangan dengan skenario ekstrim (misal: pecahan desimal ganjil 33.333 kg x Rp 1.750).
4. **Fase 4 (Deployment & Rollback Plan)**: Migrasi skema dijalankan di luar jam operasional nasabah dengan backup snapshot penuh sebelum eksekusi.
