# Laporan Audit Keamanan - banksampah-laravel

- Repo: https://github.com/theydoet666/banksampah-laravel
- Commit yang diaudit: `7e62cb4` (audit ulang, 25 Nov 2026)
- Metode: review statis kode (routes, middleware, controller, service, model, seeder, migration, view yang relevan, konfigurasi). Aplikasi dan test TIDAK dijalankan; `composer audit`/`npm audit` belum dijalankan.

Legenda: [T] Tinggi, [S] Sedang, [R] Rendah. Status: TERBUKA / SUDAH DIPERBAIKI.

---

## A. Temuan terbuka

### Tinggi

**T1. Petugas dapat mereset password nasabah di unit mana pun (takeover akun)**
- Lokasi: `app/Http/Controllers/Admin/NasabahController.php::resetPassword`; route `petugas.nasabah.reset-password`.
- Masalah: tidak ada pengecekan unit (berbeda dengan `show/edit/update`). Password baru ditampilkan di flash. Reset juga memaksa `is_active => true` sehingga akun yang sengaja dinonaktifkan hidup lagi.
- Dampak: petugas unit A login sebagai nasabah unit B, melihat data pribadi, mengajukan penarikan.
- Perbaikan: otorisasi unit (policy/trait) untuk semua aksi nasabah termasuk reset; jangan ubah `is_active`; catat di audit log.

**T2. IDOR lintas unit pada transaksi**
- Lokasi: `TransaksiController::show`, `struk`, `storeSetor`, `storeTarik`.
- Masalah: `show/struk` tidak dibatasi unit; `storeSetor/storeTarik` hanya `exists:nasabah,id`. Pola `isPetugas() && unit_id` di `TransaksiController`, `NasabahController` (index, store, show, edit, update) membuat petugas TANPA `unit_id` melihat dan mengubah semua data (fail open).
- Perbaikan: helper otorisasi unit yang fail-closed (petugas tanpa unit ditolak), dipakai di semua endpoint; `unit_id` petugas wajib di `UserController`.

**T3. Race condition pada saldo**
- Lokasi: `app/Services/TransaksiService.php`, `PenjualanService.php`, `NasabahController::inputSaldoAwal`. Tidak ada `lockForUpdate()` (0 kemunculan).
- Masalah: persetujuan penarikan, penarikan langsung, void, dan pengajuan membaca status/saldo lalu memodifikasi tanpa lock; model route-binding bisa basi.
- Dampak: persetujuan ganda memotong saldo dua kali, void ganda, saldo minus, pengajuan melebihi saldo.
- Perbaikan: `DB::transaction` + `lockForUpdate()` pada baris transaksi dan nasabah, baca ulang status di dalam lock.

**T4. Password awal nasabah baru = 6 digit terakhir No. HP**
- Lokasi: `NasabahController::store` (`$defaultPassword`), `LoginRequest` (login via No. HP / No. Rekening).
- Masalah: walau ada `must_change_password`, selama belum login pertama, siapa pun yang tahu No. HP bisa login lebih dulu, mengganti password, dan mengunci pemilik asli. Password juga dicetak di flash message.
- Perbaikan: password sementara acak (CSPRNG, alfabet tanpa karakter ambigu), tampil sekali; pertimbangkan kode aktivasi/OTP.

**T5. Kredensial dan PIN default tersebar**
- `database/seeders/UserSeeder.php`: `admin@banksampah.test` / `password`, PIN `123456` (admin & pengelola).
- `database/seeders/DatabaseSeeder.php`: `DemoSeeder` dan akun demo berjalan di SEMUA environment.
- `database/migrations/2026_11_23_000015_add_pin_to_users_table.php`: set PIN `123456` untuk admin/pengelola yang sudah ada.
- `README.md`: menyuruh `migrate:fresh --seed`.
- Perbaikan: seeder demo hanya `local/testing`; production = satu admin awal dari env/acak, PIN kosong dan wajib dibuat; migration baru untuk membatalkan PIN `123456` dan memaksa ganti password default.

**T6. Kontrol PIN lemah**
- (a) `storeTarik` oleh admin/pengelola mencairkan langsung TANPA PIN (PIN hanya di `PersetujuanPenarikanController::setujui`).
- (b) Pembatas PIN hanya `throttle:15,1` per IP; tidak ada kunci per akun, bisa dilewati dengan rotasi IP; kegagalan tidak dicatat.
- (c) `ProfileController::updatePin`: jika user belum punya PIN, tidak diminta password saat membuat PIN.
- Perbaikan: satu service PIN (5 percobaan/15 menit per akun, catat kegagalan) dipakai di semua jalur pencairan; wajibkan password saat membuat/mengubah PIN.

### Sedang

**S1. `LoginRequest` bermasalah**
- `use Illuminate\Auth\Events\Lockout;` tidak ada, sehingga saat terkena throttle terjadi error 500 (dan stack trace jika debug).
- Login via `orWhere('name', ...)` masih ada: nama tidak unik dan bisa diubah user sendiri.
- Throttle per kombinasi identitas+IP, sehingga satu IP bisa menyemprot satu password ke banyak akun (tiap identitas punya bucket sendiri). Tidak ada limiter per-IP.

**S2. Login gagal tidak tercatat di audit log**
- `AppServiceProvider` memakai listener `Failed`, tetapi login memakai `Auth::login` manual sehingga event itu tidak pernah terpicu. Brute force tidak terlihat. Input identitas yang dicatat juga tanpa batas panjang.

**S3. Registrasi**
- `/register` (Breeze) + `RegisteredUserController` masih aktif: membuat user tanpa data nasabah, tanpa throttle.
- `/register-nasabah` (`routes/auth.php`) tanpa throttle/captcha.

**S4. Saldo awal tanpa kontrol**
- `inputSaldoAwal` dan field saldo awal pada pembuatan nasabah (admin) dapat menambah saldo berkali-kali tanpa PIN, batas, persetujuan kedua, atau lock.

**S5. Pemfilteran SVG berbasis blacklist**
- `PengaturanController`: regex `<script|onload|onerror|onclick|javascript:|data:` mudah dilewati (`onbegin`, `onfocus`, entitas HTML, `foreignObject`). Logo dan favicon dilayani dari `/storage` (same-origin) sehingga berpotensi stored XSS (hanya admin yang dapat mengunggah).
- Perbaikan: hapus `svg` dari `mimes`, atau sanitasi dengan library whitelist (mis. enshrined/svg-sanitize) dan sajikan dengan `Content-Security-Policy`/`Content-Disposition`.

**S6. Route `/profile` kurang middleware**
- Group `['auth','verified']` tanpa `check.active` dan `force.password.change`.
- `ProfileController::destroy` dapat menghapus admin aktif terakhir atau nasabah yang masih bersaldo.

**S7. Formula injection pada export**
- `app/Exports/TransaksiExport.php` (nama nasabah) dan `AuditLogController::export` (CSV: `user_name`, `description` memuat nama nasabah) menulis nilai awalan `= + - @` apa adanya.

**S8. `role` masih ada di `User::$fillable`**
- Komentar di `UserController` menyebut "bukan fillable", tetapi `role` ada di `$fillable`. Risiko mass assignment jika ada `create/update` dari input yang tidak difilter.

**S9. Laravel 11.57.1 sudah melewati masa security support** (sekitar Maret 2026; mohon konfirmasi di laravel.com/docs/releases). Versi dependency lain di `composer.lock` (dompdf 3.1.6, phpspreadsheet 1.30.8, maatwebsite/excel 3.1.70) belum diperiksa terhadap advisory.

**S10. Pesan error sistem ke pengguna**
- `$e->getMessage()` ditampilkan di `TransaksiController` (baris ~98, 133, 159), `PersetujuanPenarikanController` (61, 75), `NasabahTarikController` (62), `PenjualanSampahController` (98, 132). Error SQL/path dapat bocor.

### Rendah

**R1.** Pesan reset password memuat `<strong>` yang tampil sebagai teks karena flash di-escape. JANGAN diperbaiki dengan `{!! !!}`: nama nasabah ada di pesan itu sehingga menjadi XSS.
**R2.** `.env.example`: `APP_DEBUG=true`, `APP_ENV=local`, `DB_USERNAME=root` tanpa password, `SESSION_ENCRYPT=false`. Belum ada template production.
**R3.** `AuditLogService` menelan semua exception: kegagalan mencatat aksi finansial tidak terlihat.
**R4.** Tidak ada security header (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS, CSP); tidak ada konfigurasi trusted proxy / force HTTPS.
**R5.** Uang dihitung dengan `float` (`TransaksiService`, `PenjualanService`); risiko pembulatan.
**R6.** `ResetDummyDataCommand` (`bank-sampah:reset-data`) menghapus data tanpa guard environment/konfirmasi.
**R7.** Nasabah dapat mengubah `no_hp` di profil tanpa validasi unik; `no_hp` duplikat membuat login via No. HP ambigu. (Registrasi mandiri sudah unik; jalur admin dan profil belum.)
**R8.** Upload favicon masih menerima `svg` (lihat S5); logo juga.
**R9.** Tidak ada audit log untuk void alasan: modal void belum punya input alasan (backend belum menerima `alasan`).

---

## B. Sudah diperbaiki sejak audit pertama

- Reset password nasabah dan staff memakai password acak + `must_change_password` + halaman ganti password pertama.
- Shorthand `admin/petugas/...` dibatasi `local/testing`.
- Validasi open redirect pada `url.intended`.
- Throttle ditambahkan: `setujui`, `void`, `saldo-awal`, ganti password pertama.
- Void diperiksa admin di controller.
- Role di-set lewat `User::createWithRole`.
- Fitur audit log (admin only); view merender data dengan escaping yang benar (`{{ }}` dan `x-text`, badge memakai `e()`).
- Foto nasabah divalidasi `mimetypes`.

## C. Hal yang tidak/belum diverifikasi
- Aplikasi dan test suite tidak dijalankan; blade view tidak dikompilasi.
- Tidak ada pengujian dinamis (DAST), pengecekan konfigurasi server/web server, hak akses file, atau backup.
- Advisory dependency belum dicek (`composer audit`, `npm audit`).
- Isi view tidak seluruhnya dibaca (hanya grep `{!! !!}` dan view audit-log).

## D. Urutan prioritas
1. T1, T2, T3 (kontrol akses lintas unit + locking saldo).
2. T5, T6, T4 (kredensial default, PIN, password awal).
3. S1-S4 (login, registrasi, saldo awal).
4. S5-S10, lalu R1-R9.
