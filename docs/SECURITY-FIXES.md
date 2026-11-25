# Laporan Perbaikan Audit Keamanan Bank Sampah (Security Hardening)
Branch: `security-hardening-2` | Target Lingkungan: Laravel 11.x, MySQL, Blade + Alpine

---

## 1. Pemetaan Lengkap Status Temuan Audit

| Kode | Kategori | Ringkasan Temuan | Status Perbaikan | Keterangan Solusi |
|---|---|---|---|---|
| **T1** | Otorisasi Unit | Petugas tanpa unit_id atau lintas unit mengakses data unit lain | **SELESAI** | Dibuat fail-closed trait `AuthorizesUnitAccess`. Petugas tanpa `unit_id` atau akses unit lain langsung menghasilkan HTTP 403 Forbidden. |
| **T2** | Akses Petugas | Endpoint NasabahController & TransaksiController bocor antar unit | **SELESAI** | Diterapkan pada `index`, `store`, `show`, `edit`, `update`, `resetPassword`, `setor`, `tarik`, `struk`. |
| **T3** | Concurrency / Race | Mutasi saldo dan double submit tanpa locking | **SELESAI** | Seluruh mutasi dibungkus `DB::transaction` dengan `lockForUpdate()` pada model `Transaksi` dan `Nasabah`. Validasi ulang saldo & status di dalam lock. |
| **T4** | Default Password | Password nasabah baru/reset mudah ditebak (6 digit HP) | **SELESAI** | Diganti password acak 10 karakter alfanumerik bebas ambigu (`RandomPassword::generate(10)`), flash session sekali pakai, `must_change_password=true`. |
| **T5** | Kredensial Seeder | Akun demo tersimpan di production dengan password lemah | **SELESAI** | Seeder akun demo dibatasi hanya `local`/`testing`. Non-local membuat 1 admin terisolasi dari env dengan password acak & PIN null. |
| **T6** | PIN Transaksi | PIN rentan brute force dan pencairan langsung admin tanpa PIN | **SELESAI** | Dibuat `PinGuard::check` dengan RateLimiter per akun (5 gagal / 15 menit), wajib PIN pada pencairan langsung admin di `tarik/create`. |
| **S1** | Login Rate Limit | Lockout event missing & login via nama | **SELESAI** | Ditambahkan event `Lockout`, hapus `orWhere('name')`, rate limiter kedua berbasis IP (20/5m), shorthand role diisolasi ke local/testing. |
| **S2** | Audit Login Gagal | Percobaan login gagal tidak tercatat | **SELESAI** | Dicatat otomatis ke `AuditLogService` dengan masking identitas (maks 100 karakter, tanpa mencatat password). |
| **S3** | Route Registrasi | Route Breeze `/register` terbuka umum | **SELESAI** | Menghapus controller `RegisteredUserController` & view registrasi, route `/register` mengembalikan HTTP 404, throttle diperketat pada registrasi nasabah & login. |
| **S4** | Saldo Awal | Input saldo awal rentan injeksi saldo tanpa otorisasi | **SELESAI** | Wajib verifikasi PIN Administrator lewat `PinGuard`, pembatasan plafon maksimal, pencegahan duplikasi data ganda, dibungkus DB lock. |
| **S5** | Upload Logo/Favicon | Upload SVG rentan XSS tersimpan | **SELESAI** | Ekstensi `svg` dihapus dari mime types upload logo dan favicon, menghapus blacklist regex yang rapuh, hanya izinkan raster/vector aman. |
| **S6** | Guard Profil | Admin terakhir & nasabah bersaldo bisa hapus akun | **SELESAI** | Ditambahkan proteksi pada `ProfileController::destroy`: tolak hapus jika admin aktif terakhir atau nasabah masih memiliki saldo/saldo tertahan. |
| **S7** | Formula Injection | Export CSV / Excel rentan formula injection | **SELESAI** | Dibuat `SpreadsheetSanitizer::sanitizeRow` yang mengawali nilai berawalan `=`, `+`, `-`, `@`, `\t`, `\r` dengan tanda kutip tunggal (`'`). |
| **S8** | Mass Assignment | Kolom `role` ada di `$fillable` model User | **SELESAI** | Menghapus `'role'` dari `$fillable`. Pembuatan akun user wajib melalui helper `createWithRole()` atau `forceFill()`. |
| **S9** | Dependensi | Advisory kerentanan framework | **SELESAI** | Audit lengkap via `composer audit` & `npm audit`. Mitigasi internal diterapkan dan rencana upgrade didokumentasikan di `docs/UPGRADE-PLAN.md`. |
| **S10** | Error Masking | Stack trace dan `$e->getMessage()` bocor ke pengguna | **SELESAI** | Menyamarkan seluruh pesan error sistem menjadi pesan ramah pengguna; hanya melewatkan pesan pada `DomainException` atau `InsufficientBalanceException`. |
| **R1** | XSS Flash Message | Password dicetak mentah dengan `{!! !!}` | **SELESAI** | Menggunakan komponen flash terpisah yang aman dengan tag `<code>` tanpa unescaped blade output. |
| **R2** | Config Hardening | Konfigurasi production rentan | **SELESAI** | Dibuat `.env.production.example` (APP_DEBUG=false, FORCE_HTTPS=true, SESSION_SECURE_COOKIE=true, SESSION_ENCRYPT=true). |
| **R3** | Audit Log Catch | Error handling log audit berpotensi silent fail | **SELESAI** | Ditambahkan logging error terperinci via `Log::channel('single')->error()` dengan konteks lengkap tanpa memutus flow pengguna. |
| **R4** | Security Headers | Tidak ada security headers HTTP | **SELESAI** | Dibuat middleware `SecurityHeaders` (X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, HSTS saat HTTPS) di grup web. |
| **R5** | Presisi Keuangan | Risiko selisih pembulatan floating point uang | **SELESAI** | Dilakukan `round(..., 2)` pada kalkulasi subtotal dan total, proposal migrasi BCMath didokumentasikan di `docs/PROPOSAL-UANG.md`. |
| **R6** | Command Reset | Command `bank-sampah:reset-data` berbahaya di production | **SELESAI** | Menolak eksekusi di lingkungan production tanpa flag `--force` dan menambahkan konfirmasi interaktif. |
| **R7** | Unik No HP | No HP nasabah bisa duplikat | **SELESAI** | Ditambahkan validasi `unique:nasabah,no_hp` pada store, update admin, dan edit profil nasabah. |
| **R8** | Favicon Cleanup | Favicon SVG blacklist dihapus | **SELESAI** | Favicon SVG dihentikan, sanitasi regex dihilangkan demi whitelist mimes aman. |
| **R9** | Alasan Void | Pembatalan transaksi tanpa alasan audit | **SELESAI** | Modal void dan controller menerima parameter `alasan` dan dicatat ke dalam audit log & kolom transaksi. |

---

## 2. Riwayat Commit Keamanan pada Branch `security-hardening-2`

1. `cafc3b0` - `fix(security): unit access authorization and saldo concurrency locking (T1-T3)`
2. `bd943f3` - `fix(security): pin guard, credential seeding and initial password hardening (T4-T6, R7)`
3. `2eb05b7` - `fix(security): login rate limiting, failed login audit and breeze register removal (S1-S3, R3)`
4. `667e493` - `fix(security): saldo awal control, profile guard, upload and spreadsheet sanitization (S4-S8, R8)`
5. `411f7f2` - `fix(security): error masking, security headers, production config and void reason (S10, R2, R4-R6, R9)`
6. `TBD` - `docs(security): dependency audit and upgrade plan documentation (S9)`
