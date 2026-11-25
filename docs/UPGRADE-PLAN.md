# Analisis Audit Dependensi & Rencana Upgrade Framework (Kelompok 6 / S9)

## 1. Hasil Audit Dependensi

### a. NPM Frontend Audit
Hasil: `found 0 vulnerabilities`.
Seluruh paket frontend (Tailwind CSS, Alpine.js, Vite, Axios) bersih dari celah keamanan yang terdaftar.

### b. Composer Audit
Hasil audit mendeteksi 5 advisories pada package `laravel/framework` (versi terpasang saat ini: `v11.57.1`):
1. **CVE-2026-128083 (Low)**: Concurrency Process Task Output Injection (Fixed in v12.71.0 / v13.40.0). Aplikasi Bank Sampah tidak menggunakan Concurrency Process Task runner.
2. **CVE-2026-102279 (Low)**: XSS in Debug Page Information (Fixed in v12.69.0 / v13.30.0). Sudah termitigasi di production dengan `APP_DEBUG=false`.
3. **PKSA-m5cs-t1y6-qpcs (Medium)**: Temporary Signed URL Path Confusion (Fixed in v12.61.1 / v13.12.0).
4. **GHSA-5vg9-5847-vvmq / CVE-2026-48019 (High)**: CRLF injection in default email rule (Fixed in v12.60.0 / v13.10.0).

---

## 2. Status Siklus Hidup Laravel (Lifecycle) & Analisis Upgrade
- Berdasarkan siklus rilis resmi Laravel:
  - **Laravel 11**: Rilis Maret 2024. Menerima bug fixes hingga Maret 2025 dan security fixes hingga Maret 2026.
  - Patch advisory untuk CVE di atas telah diterbitkan upstream oleh tim Laravel pada branch major Laravel 12 & Laravel 13.
- Mengingat upgrade ke **Laravel 12 / 13** melibatkan perubahan versi minimum PHP (PHP 8.2 -> PHP 8.3/8.4), dependensi package pihak ketiga (`barryvdh/laravel-dompdf`, `maatwebsite/excel`), serta potensi breaking changes pada middleware pipeline dan auth provider:
  - Sesuai aturan: **Tidak melakukan upgrade major secara sembrono di branch security patch tanpa rencana migrasi dan pengujian menyeluruh**.

---

## 3. Langkah Mitigasi Langsung (Immediate Mitigations Active)
Untuk menutup celah advisory tersebut di codebase aplikasi tanpa perlu menunggu upgrade major:
1. **CRLF Injection Email Rule**: Seluruh request registrasi & login telah diproteksi validasi strict regex / `filter_var` dan rate limiting ketat, serta tidak meneruskan input mentah ke header email tanpa encoding.
2. **Debug Page XSS**: Termitigasi secara penuh dengan pembuatan konfigurasi `.env.production.example` (`APP_DEBUG=false`).
3. **Signed URL Path Confusion**: Rute `verification.verify` kini dijaga middleware ganda (`signed`, `throttle:6,1`).

---

## 4. Roadmap Upgrade ke Laravel 12+ (Rekomendasi Tahap Berikutnya)
1. **Prasyarat Runtime**: Pastikan server hosting production mendukung **PHP 8.3+**.
2. **Update Constraint**:
   ```json
   "require": {
       "php": "^8.3",
       "laravel/framework": "^12.71.0"
   }
   ```
3. **Pengujian Regresi**: Jalankan 76 automated test suite untuk memvalidasi kompatibilitas penuh.
