# Rekomendasi Pengembangan: Onboarding, Rekap Password & Aktivasi Mandiri Nasabah

> **Dokumen:** Proposal & Rencana Pengembangan Fitur  
> **Tanggal:** 26 November 2026  
> **Status:** Disimpan untuk pengembangan tahap berikutnya  
> **Konteks:** Sistem saat ini menghasilkan kata sandi sementara acak 10 karakter (`RandomPassword::generate(10)`) dengan `must_change_password = true` dan menyimpannya sebagai hash Bcrypt satu arah di `users.password` (tidak dapat didekripsi kembali demi keamanan).

---

## 1. Latar Belakang & Prinsip Keamanan

Setelah kebijakan keamanan diperketat (mengganti password default 6 digit terakhir No. HP menjadi kata sandi acak 10 karakter), muncul kebutuhan operasional saat admin/petugas mendaftarkan banyak nasabah sekaligus:

1. Bagaimana admin bisa merekap akun nasabah baru beserta kata sandi sementaranya ke Excel untuk dibagikan ke warga?
2. Bagaimana agar nasabah yang sudah didaftarkan petugas bisa mengaktifkan akunnya sendiri tanpa harus merepotkan petugas membagikan password satu per satu?

**Batasan Keamanan Utama:**
* Password asli (*plain text*) **tidak boleh** disimpan permanen di database.
* Setiap akses massal terhadap kredensial maupun aktivasi mandiri harus dilindungi **otorisasi ketat**, **rate limiting**, dan **audit log**.

---

## 2. Perbandingan 3 Opsi

| Aspek | Opsi 1: Enkripsi Sementara + Export Excel | Opsi 2: Generate & Export Sekaligus (*On-Demand*) | Opsi 3: Aktivasi Mandiri via No. Rekening + NIK |
| :--- | :--- | :--- | :--- |
| **Cara Kerja** | Password sementara dienkripsi AES-256 (`temp_password_encrypted`), otomatis dihapus (`NULL`) setelah nasabah login pertama & ganti password. Admin bisa export Excel kapan saja sebelum nasabah login. | Tidak menyimpan password sementara sama sekali. Saat Admin klik Export Excel, sistem langsung men-generate password baru untuk nasabah yang `must_change_password = true` lalu mengunduh Excel. | Nasabah membuka halaman `/aktivasi-nasabah`, memverifikasi **No. Rekening + NIK + No. HP**, lalu membuat password pribadinya sendiri (berlaku 1 kali saja). |
| **Kemudahan Admin** | Sangat mudah — input dulu sepanjang hari, export Excel di akhir hari tanpa mengubah password yang sudah terlanjur dicatat. | Mudah — cukup 1 klik saat siap dibagikan, namun password sebelumnya akan tertimpa setiap kali export. | Sangat mudah — admin/petugas cukup beri tahu No. Rekening saja. |
| **Keamanan Data** | Tinggi (enkripsi AES-256 + wajib PIN Admin saat export + auto-wipe saat ganti password). | Tertinggi (tidak ada salinan password sementara di DB, wajib PIN Admin saat export). | Tinggi **jika** dibatasi hanya 1 kali saat `must_change_password = true` + 3 faktor (`No. Rekening` + `NIK` + `No. HP`) + rate limit. |
| **Ketergantungan Data** | Tidak bergantung pada kelengkapan NIK. | Tidak bergantung pada kelengkapan NIK. | Membutuhkan data **NIK** nasabah terisi saat pendaftaran (saat ini `nik` bersifat opsional). |

---

## 3. Rincian Teknis Masing-Masing Opsi

### Opsi 1 — Penyimpanan Sementara Terenkripsi + Export Excel Admin *(Direkomendasikan untuk Rekap Massal)*
* **Migrasi Baru:** Tambah kolom `temp_password_encrypted` (`TEXT NULL`) pada tabel `users`.
* **Alur Penyimpanan:**
  * Di `NasabahController::store()` dan `resetPassword()`, simpan `Crypt::encryptString($defaultPassword)` ke `temp_password_encrypted`.
  * Di `FirstLoginPasswordController::update()`, `Nasabah\ProfilController::password()`, dan `PasswordController::update()`, langsung hapus (`temp_password_encrypted = null`) saat nasabah mengganti password.
* **Fitur Export Excel (Khusus `role:admin`):**
  * Endpoint dilindungi middleware `role:admin` dan **verifikasi PIN 6-digit Administrator (`PinGuard::check`)** sebelum file diunduh.
  * Menggunakan `Maatwebsite\Excel` + `SpreadsheetSanitizer` (anti CSV/Excel Formula Injection).
  * Kolom Excel: `No. Rekening | Nama Nasabah | Unit | No. HP | Email Akun | Status Password | Kata Sandi Sementara`.
  * Bagi nasabah yang sudah mengganti password (`must_change_password = false`), kolom kata sandi tertulis `Sudah diganti oleh nasabah`.
  * Opsi tambahan saat export: centang *"Generate ulang untuk nasabah lama yang belum memiliki password sementara"*.
  * Setiap proses export dicatat ke `auditLog()`.

### Opsi 2 — Generate & Export Sekaligus (*On-Demand Reset*)
* **Tanpa Perubahan Skema DB:** Tidak memerlukan kolom `temp_password_encrypted`.
* **Alur:**
  * Admin memilih Unit / filter nasabah yang masih berstatus `must_change_password = true` dan memasukkan **PIN 6-digit Admin**.
  * Di dalam `DB::transaction`, sistem membuatkan password acak baru (`RandomPassword::generate(10)`) untuk daftar nasabah tersebut, memperbarui hash `users.password`, mencatat `auditLog()`, lalu langsung men-stream file `.xlsx` ke browser.
* **Catatan:** Cocok jika kebijakan organisasi melarang penyimpanan salinan password sementara meski dalam bentuk terenkripsi.

### Opsi 3 — Aktivasi Akun Mandiri Nasabah (`No. Rekening` + `NIK` + `No. HP`)
* **Tujuan:** Menghilangkan kebutuhan membagikan password sementara untuk nasabah yang sudah memiliki data NIK valid di sistem.
* **Rute & Controller Baru (`NasabahActivationController`):**
  * `GET /aktivasi-nasabah` dan `POST /aktivasi-nasabah` (middleware `guest` + `throttle:5,10`).
* **Syarat Verifikasi Ketat (One-Time Claim):**
  1. Nasabah ditemukan berdasarkan `no_rekening`, berstatus `aktif`, dan `user->is_active === true`.
  2. Akun **belum pernah diaktivasi** (`$user->must_change_password === true`). Setelah aktif, rute ini **tidak dapat dipakai lagi** oleh akun tersebut (mencegah pengambilalihan akun jika NIK bocor).
  3. Data `nik` pada tabel `nasabah` **tidak kosong** dan cocok persis (16 digit).
  4. Data `no_hp` cocok sebagai faktor verifikasi tambahan.
  5. Rate limiter per `no_rekening` + per IP dan pencatatan kegagalan ke `auditLog()` untuk mencegah tebak-tebakan NIK.
* **Hasil Aktivasi:** Password baru pilihan nasabah (memenuhi `Password::defaults()`) langsung disimpan, `must_change_password = false`, `temp_password_encrypted = null` (jika Opsi 1 dipakai), dan nasabah otomatis login ke dashboard.

---

## 4. Rekomendasi Pengembangan Selanjutnya

Kombinasi terbaik untuk operasional Bank Sampah Desa adalah **menggabungkan Opsi 3 + Opsi 1**:
1. **Jalur Utama (Opsi 3):** Nasabah yang sudah didaftarkan beserta NIK-nya cukup diberikan **No. Rekening**, lalu mengaktifkan akun dan membuat password sendiri lewat halaman `/aktivasi-nasabah`.
2. **Jalur Cadangan / Massal (Opsi 1):** Untuk warga yang tidak membawa NIK saat pendaftaran atau pendaftaran kolektif RT/Dusun, Admin tetap dapat mengunduh rekap Excel akun & kata sandi sementara menggunakan PIN otorisasi Administrator.
