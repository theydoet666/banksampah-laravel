<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BankSampahTest extends TestCase
{
    use DatabaseTransactions;

    protected ?User $admin = null;
    protected ?User $petugas = null;
    protected ?User $nasabahUser = null;
    protected ?Nasabah $nasabah = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!User::where('role', 'admin')->exists()) {
            $this->artisan('db:seed');
        }

        $this->admin = User::where('role', 'admin')->first();
        if ($this->admin) {
            $this->admin->pin = \Illuminate\Support\Facades\Hash::make('123456');
            $this->admin->save();
        }
        $this->petugas = User::where('role', 'petugas')->first();
        $this->nasabahUser = User::where('email', 'nasabah@banksampah.test')->first()
            ?? User::where('role', 'nasabah')->first();

        if (! $this->nasabahUser) {
            $unit = \App\Models\Unit::first() ?? \App\Models\Unit::create(['nama' => 'Unit Test', 'kode_unit' => 'UNT-01', 'status' => 'aktif']);
            $this->nasabahUser = User::create([
                'name' => 'Budi Santoso',
                'email' => 'nasabah@banksampah.test',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'nasabah',
                'is_active' => true,
                'unit_id' => $unit->id,
            ]);
        } else {
            $this->nasabahUser->password = \Illuminate\Support\Facades\Hash::make('password');
            $this->nasabahUser->save();
        }

        $this->nasabah = Nasabah::where('user_id', $this->nasabahUser->id)->first();
        if (! $this->nasabah) {
            $unit = \App\Models\Unit::first();
            $this->nasabah = Nasabah::create([
                'user_id' => $this->nasabahUser->id,
                'unit_id' => $unit?->id,
                'no_rekening' => 'BS-2026-00001',
                'nama' => 'Budi Santoso',
                'no_hp' => '081234567890',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Mawar No. 12',
                'saldo' => 50000,
                'status' => 'aktif',
            ]);
        }
    }

    public function test_login_post_redirects_per_role(): void
    {
        // Admin login
        $response = $this->post('/login', [
            'email' => 'admin@banksampah.test',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('admin.dashboard'));

        // Petugas login
        $this->post('/logout');
        $response = $this->post('/login', [
            'email' => 'petugas@banksampah.test',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('petugas.dashboard'));

        // Nasabah login
        $this->post('/logout');
        $response = $this->post('/login', [
            'email' => $this->nasabahUser->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('nasabah.dashboard'));

        // Pengelola login
        $this->post('/logout');
        $response = $this->post('/login', [
            'email' => 'pengelola@banksampah.test',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('admin.dashboard'));

        // Wrong password
        $this->post('/logout');
        $response = $this->post('/login', [
            'email' => 'admin@banksampah.test',
            'password' => 'wrong-password',
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_dashboard_url_redirects_per_role(): void
    {
        // Admin
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));

        // Petugas
        $response = $this->actingAs($this->petugas)->get('/dashboard');
        $response->assertRedirect(route('petugas.dashboard'));

        // Nasabah
        $response = $this->actingAs($this->nasabahUser)->get('/dashboard');
        $response->assertRedirect(route('nasabah.dashboard'));
    }

    public function test_role_access_control_forbidden(): void
    {
        // Petugas accessing admin dashboard should be 403 Forbidden
        $response = $this->actingAs($this->petugas)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Nasabah accessing admin dashboard should be 403 Forbidden
        $response = $this->actingAs($this->nasabahUser)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_nasabah_number_generation(): void
    {
        $noRekening = Nasabah::generateNoRekening();
        $year = date('Y');
        $this->assertStringStartsWith("BS-{$year}-", $noRekening);
    }

    public function test_setor_sampah_increases_nasabah_balance(): void
    {
        $jenis = JenisSampah::where('is_active', true)->first();
        $saldoAwal = $this->nasabah->saldo;
        $berat = 5.0; // 5 kg
        $subtotal = $berat * $jenis->harga_per_satuan;

        $response = $this->actingAs($this->petugas)->post(route('petugas.transaksi.setor.store'), [
            'nasabah_id' => $this->nasabah->id,
            'tanggal_transaksi' => now()->toDateString(),
            'catatan' => 'Test setor PHPUnit',
            'details' => [
                [
                    'jenis_sampah_id' => $jenis->id,
                    'berat' => $berat,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->nasabah->refresh();
        $this->assertEquals($saldoAwal + $subtotal, $this->nasabah->saldo);
    }

    public function test_tarik_saldo_insufficient_balance_fails(): void
    {
        $saldoAwal = $this->nasabah->saldo;
        $nominalBerlebih = $saldoAwal + 1000000;

        $response = $this->actingAs($this->petugas)->post(route('petugas.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => $nominalBerlebih,
            'tanggal_transaksi' => now()->toDateString(),
            'catatan' => 'Test tarik melebihi saldo',
        ]);

        $response->assertSessionHas('error');
        $this->nasabah->refresh();
        $this->assertEquals($saldoAwal, $this->nasabah->saldo);
    }

    public function test_tarik_saldo_success_decreases_balance(): void
    {
        $this->nasabah->saldo = 50000;
        $this->nasabah->save();

        // Admin pengelola desa mencatat dan mencairkan penarikan tunai secara langsung
        $response = $this->actingAs($this->admin)->post(route('admin.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => 20000,
            'tanggal_transaksi' => now()->toDateString(),
            'catatan' => 'Test tarik sukses',
        ]);

        $response->assertRedirect();
        $this->nasabah->refresh();
        $this->assertEquals(30000, $this->nasabah->saldo);
    }

    public function test_void_setor_transaksi_reverts_balance(): void
    {
        $jenis = JenisSampah::where('is_active', true)->first();
        $this->nasabah->saldo = 50000;
        $this->nasabah->save();

        $berat = 2.0;
        $hargaSatuan = 5000;
        $jenis->harga_per_satuan = $hargaSatuan;
        $jenis->save();

        $transaksi = Transaksi::create([
            'kode_transaksi' => Transaksi::generateKodeTransaksi(),
            'nasabah_id' => $this->nasabah->id,
            'jenis' => 'setor',
            'total_berat' => $berat,
            'total_nilai' => $berat * $hargaSatuan,
            'status' => 'aktif',
            'petugas_id' => $this->petugas->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);
        $this->nasabah->increment('saldo', 10000);
        $this->assertEquals(60000, $this->nasabah->saldo);

        // Void transaksi oleh Admin (POST)
        $response = $this->actingAs($this->admin)->post(route('admin.transaksi.void', $transaksi));
        $response->assertRedirect();

        $transaksi->refresh();
        $this->assertEquals('void', $transaksi->status);

        $this->nasabah->refresh();
        $this->assertEquals(50000, $this->nasabah->saldo);
    }

    public function test_export_laporan_accessible(): void
    {
        // Test PDF export
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Test Excel export
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.excel'));
        $response->assertStatus(200);
    }

    public function test_multi_unit_management_and_scoping(): void
    {
        // 1. Admin membuat unit baru
        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), [
            'kode_unit' => 'U-99',
            'nama' => 'Unit Uji Coba RW 99',
            'nama_ketua' => 'Bpk. Penguji',
            'no_hp' => '08999999999',
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', ['kode_unit' => 'U-99']);

        $unit = \App\Models\Unit::where('kode_unit', 'U-99')->first();

        // 2. Petugas yang ditugaskan ke Unit U-99
        $petugasKhusus = User::create([
            'name' => 'Petugas Khusus U99',
            'email' => 'petugas99@banksampah.test',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        // 3. Nasabah didaftarkan di Unit U-99
        $nasabahU99 = Nasabah::create([
            'unit_id' => $unit->id,
            'no_rekening' => 'BS-2026-99999',
            'nama' => 'Warga Unit 99',
            'jenis_kelamin' => 'L',
            'alamat' => 'RT 01 RW 99',
            'no_hp' => '089988776655',
            'saldo' => 0,
            'status' => 'aktif',
            'registered_by' => $petugasKhusus->id,
        ]);

        // Petugas U-99 hanya melihat nasabah di unitnya
        $response = $this->actingAs($petugasKhusus)->get(route('petugas.nasabah.index'));
        $response->assertStatus(200);
        $response->assertSee('Warga Unit 99');
    }

    public function test_penjualan_sampah_ke_pengepul_and_laba_rugi(): void
    {
        $pengepul = \App\Models\Pengepul::first() ?? \App\Models\Pengepul::create([
            'nama_perusahaan' => 'PT Daur Ulang Lestari',
            'penanggung_jawab' => 'Pak Hendra',
            'no_hp' => '081234567899',
            'alamat' => 'Kawasan Industri',
            'status' => 'aktif',
        ]);
        $this->assertNotNull($pengepul);

        // Ambil stok yang ada
        $penjualanService = app(\App\Services\PenjualanService::class);
        $stokGudang = $penjualanService->getStokSampahGudang();
        $available = $stokGudang->filter(fn($j) => $j['sisa_stok'] >= 1.0)->first();

        if ($available) {
            $beratJual = 0.5; // jual setengah kg
            $hargaJual = $available['harga_beli'] + 1000;

            // Catat penjualan
            $response = $this->actingAs($this->admin)->post(route('admin.penjualan.store'), [
                'pengepul_id' => $pengepul->id,
                'tanggal_penjualan' => now()->toDateString(),
                'status_pembayaran' => 'lunas',
                'details' => [
                    [
                        'jenis_sampah_id' => $available['id'],
                        'berat' => $beratJual,
                        'harga_jual_satuan' => $hargaJual,
                    ],
                ],
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('penjualan_sampah', ['pengepul_id' => $pengepul->id]);
        }

        // Test monitoring stok gudang
        $response = $this->actingAs($this->admin)->get(route('admin.penjualan.stok'));
        $response->assertStatus(200);

        // Test Laporan Laba/Rugi
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.laba-rugi'));
        $response->assertStatus(200);

        // Test Laporan Laba/Rugi PDF
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.laba-rugi.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_update_bank_sampah_branding_and_upload_logo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $logoFile = \Illuminate\Http\UploadedFile::fake()->image('bank_logo.png', 200, 200);
        $faviconFile = \Illuminate\Http\UploadedFile::fake()->image('favicon.png', 32, 32);

        $response = $this->actingAs($this->admin)->put(route('admin.pengaturan.update'), [
            'nama_bank_sampah' => 'Bank Sampah Berkah Mandiri',
            'tagline' => 'Bersih Lingkunganku, Sejahtera Wargaku',
            'no_telp' => '081234567890',
            'email' => 'admin@berkahmandiri.test',
            'alamat' => 'Jl. Hijau Lestari No. 10',
            'tahun_berdiri' => '2024',
            'logo' => $logoFile,
            'favicon' => $faviconFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Bank Sampah Berkah Mandiri', \App\Models\AppSetting::get('nama_bank_sampah'));
        $this->assertEquals('Bersih Lingkunganku, Sejahtera Wargaku', \App\Models\AppSetting::get('tagline'));
        
        $logoPath = \App\Models\AppSetting::get('logo');
        $this->assertNotEmpty($logoPath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($logoPath);

        $faviconPath = \App\Models\AppSetting::get('favicon');
        $this->assertNotEmpty($faviconPath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($faviconPath);

        // Pastikan render favicon di halaman
        $pageResponse = $this->actingAs($this->admin)->get(route('admin.pengaturan.index'));
        $pageResponse->assertSee($faviconPath);
        $pageResponse->assertSee('rel="icon"', false);

        // Test hapus favicon
        $deleteFaviconResponse = $this->actingAs($this->admin)->put(route('admin.pengaturan.update'), [
            'nama_bank_sampah' => 'Bank Sampah Berkah Mandiri',
            'no_telp' => '081234567890',
            'email' => 'admin@berkahmandiri.test',
            'alamat' => 'Jl. Hijau Lestari No. 10',
            'hapus_favicon' => '1',
        ]);
        $deleteFaviconResponse->assertRedirect();
        $this->assertNull(\App\Models\AppSetting::get('favicon'));
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($faviconPath);
    }

    public function test_nasabah_pengajuan_tarik_and_admin_approval_workflow(): void
    {
        // 1. Pastikan nasabah punya saldo dan bersihkan transaksi pending sebelumnya
        Transaksi::where('nasabah_id', $this->nasabah->id)->where('status', 'menunggu_persetujuan')->delete();
        $this->nasabah->update(['saldo' => 100000]);
        $saldoAwal = (float) $this->nasabah->fresh()->saldo;
        $this->assertEquals(100000, $saldoAwal);

        // 2. Nasabah login dan mengajukan tarik Rp 30.000
        $response = $this->actingAs($this->nasabahUser)->post(route('nasabah.tarik.store'), [
            'nominal' => 30000,
            'metode_penarikan' => 'tunai_desa',
            'catatan' => 'Untuk keperluan belanja',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi saldo: saldo utama belum berkurang, tetapi saldo_tertahan = 30000 dan saldo_tersedia = 70000
        $nasabahRefreshed = $this->nasabah->fresh();
        $this->assertEquals(100000, (float) $nasabahRefreshed->saldo);
        $this->assertEquals(30000, $nasabahRefreshed->saldo_tertahan);
        $this->assertEquals(70000, $nasabahRefreshed->saldo_tersedia);

        // Cari transaksi pengajuan
        $transaksiPengajuan = Transaksi::where('nasabah_id', $this->nasabah->id)
            ->where('status', 'menunggu_persetujuan')
            ->first();
        $this->assertNotNull($transaksiPengajuan);
        $this->assertEquals(30000, (float) $transaksiPengajuan->total_nilai);

        // 3. Nasabah mencoba mengajukan lebih dari saldo_tersedia (misal Rp 80.000) -> Gagal validasi
        $responseFailed = $this->actingAs($this->nasabahUser)->post(route('nasabah.tarik.store'), [
            'nominal' => 80000,
            'metode_penarikan' => 'tunai_desa',
        ]);
        $responseFailed->assertSessionHasErrors('nominal');

        // 4. Admin melihat antrean approval
        $responseAntrean = $this->actingAs($this->admin)->get(route('admin.persetujuan-tarik.index'));
        $responseAntrean->assertStatus(200);
        $responseAntrean->assertSee($transaksiPengajuan->kode_transaksi);

        // 5. Admin menyetujui penarikan dengan PIN yang benar
        $responseApprove = $this->actingAs($this->admin)->post(route('admin.persetujuan-tarik.setujui', $transaksiPengajuan), [
            'pin' => '123456',
        ]);
        $responseApprove->assertRedirect();
        $responseApprove->assertSessionHas('success');

        // Saldo nasabah resmi berkurang
        $nasabahAfterApprove = $this->nasabah->fresh();
        $this->assertEquals(70000, (float) $nasabahAfterApprove->saldo);
        $this->assertEquals(0, $nasabahAfterApprove->saldo_tertahan);
        $this->assertEquals(70000, $nasabahAfterApprove->saldo_tersedia);
        $this->assertEquals('disetujui', $transaksiPengajuan->fresh()->status);
        $this->assertEquals($this->admin->id, $transaksiPengajuan->fresh()->disetujui_oleh);

        // 6. Test penolakan: Petugas unit mengajukan tarik Rp 20.000 untuk nasabah
        $responsePetugas = $this->actingAs($this->petugas)->post(route('petugas.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => 20000,
            'metode_penarikan' => 'tunai',
            'tanggal_transaksi' => now()->toDateString(),
            'catatan' => 'Diajukan di loket unit',
        ]);
        $responsePetugas->assertRedirect();

        $pengajuanPetugas = Transaksi::where('nasabah_id', $this->nasabah->id)
            ->where('status', 'menunggu_persetujuan')
            ->first();
        $this->assertNotNull($pengajuanPetugas);

        // Admin menolak pengajuan petugas
        $responseReject = $this->actingAs($this->admin)->post(route('admin.persetujuan-tarik.tolak', $pengajuanPetugas), [
            'alasan_penolakan' => 'Kas tunai hari ini belum siap, silakan ambil besok.',
        ]);
        $responseReject->assertRedirect();
        $responseReject->assertSessionHas('success');

        $this->assertEquals('ditolak', $pengajuanPetugas->fresh()->status);
        $this->assertEquals('Kas tunai hari ini belum siap, silakan ambil besok.', $pengajuanPetugas->fresh()->alasan_penolakan);

        // Saldo tidak berkurang setelah ditolak
        $nasabahAfterReject = $this->nasabah->fresh();
        $this->assertEquals(70000, (float) $nasabahAfterReject->saldo);
        $this->assertEquals(0, $nasabahAfterReject->saldo_tertahan);
    }

    public function test_pengelola_role_permissions_and_restrictions(): void
    {
        $pengelola = User::where('role', 'pengelola')->first();
        $this->assertNotNull($pengelola);

        // 1. Pengelola diizinkan mengakses Dashboard
        $response = $this->actingAs($pengelola)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // 2. Pengelola diizinkan mengakses Penjualan Sampah & Mitra Pengepul
        $response = $this->actingAs($pengelola)->get(route('admin.penjualan.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($pengelola)->get(route('admin.penjualan.stok'));
        $response->assertStatus(200);

        $response = $this->actingAs($pengelola)->get(route('admin.pengepul.index'));
        $response->assertStatus(200);

        // 3. Pengelola diizinkan mengakses Persetujuan Tarik Saldo
        $response = $this->actingAs($pengelola)->get(route('admin.persetujuan-tarik.index'));
        $response->assertStatus(200);

        // 4. Pengelola diizinkan mengakses Laporan Laba/Rugi & Laporan Transaksi
        $response = $this->actingAs($pengelola)->get(route('admin.laporan.laba-rugi'));
        $response->assertStatus(200);

        $response = $this->actingAs($pengelola)->get(route('admin.laporan.index'));
        $response->assertStatus(200);

        // 5. RESTRICTIONS: Pengelola DILARANG mengakses Manajemen Pengguna (403)
        $response = $this->actingAs($pengelola)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // 6. RESTRICTIONS: Pengelola DILARANG mengakses Manajemen Unit (403)
        $response = $this->actingAs($pengelola)->get(route('admin.units.index'));
        $response->assertStatus(403);

        // 7. RESTRICTIONS: Pengelola DILARANG mengakses Pengaturan Identitas/Logo (403)
        $response = $this->actingAs($pengelola)->get(route('admin.pengaturan.index'));
        $response->assertStatus(403);
    }

    public function test_multi_identifier_login_and_auto_account_onboarding(): void
    {
        $nasabah = $this->nasabah;
        $this->assertNotNull($nasabah);
        $this->assertNotNull($nasabah->user);

        // Pastikan password nasabah diketahui
        $nasabah->user->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);

        // 1. Login nasabah via Nomor Rekening
        $this->post('/logout');
        $responseRekening = $this->post('/login', [
            'email' => $nasabah->no_rekening,
            'password' => 'password',
        ]);
        $responseRekening->assertRedirect(route('nasabah.dashboard'));
        $this->assertAuthenticatedAs($nasabah->user);

        // 2. Login nasabah via Nomor HP
        $this->post('/logout');
        $responseHp = $this->post('/login', [
            'email' => $nasabah->no_hp,
            'password' => 'password',
        ]);
        $responseHp->assertRedirect(route('nasabah.dashboard'));
        $this->assertAuthenticatedAs($nasabah->user);

        // 3. Petugas mendaftarkan nasabah baru di loket pos -> user otomatis terbuat
        $unit = \App\Models\Unit::first();
        $uniquePhone = '0898' . rand(10000000, 99999999);
        $expectedDefaultPassword = substr($uniquePhone, -6);

        $responseDaftar = $this->actingAs($this->admin)->post(route('admin.nasabah.store'), [
            'nama' => 'Warga Baru Auto Account',
            'no_hp' => $uniquePhone,
            'jenis_kelamin' => 'L',
            'alamat' => 'Dusun Percontohan RT 01',
            'unit_id' => $unit->id,
            'status' => 'aktif',
        ]);
        $responseDaftar->assertRedirect(route('admin.nasabah.index'));
        $responseDaftar->assertSessionHas('success');

        $newNasabah = Nasabah::where('no_hp', $uniquePhone)->first();
        $this->assertNotNull($newNasabah);
        $this->assertNotNull($newNasabah->user_id);
        $this->assertEquals('nasabah', $newNasabah->user->role);

        // Nasabah baru langsung bisa login menggunakan No. Rekening & Password Default 6 Digit No. HP
        $this->post('/logout');
        $responseLoginNew = $this->post('/login', [
            'email' => $newNasabah->no_rekening,
            'password' => $expectedDefaultPassword,
        ]);
        $responseLoginNew->assertRedirect(route('nasabah.dashboard'));
        $this->assertAuthenticatedAs($newNasabah->user);

        // 4. Warga melakukan registrasi mandiri via web (/register-nasabah)
        $this->post('/logout');
        $selfRegisterPhone = '0877' . rand(10000000, 99999999);
        $responseSelfReg = $this->post('/register-nasabah', [
            'nama' => 'Warga Mandiri Online',
            'no_hp' => $selfRegisterPhone,
            'jenis_kelamin' => 'P',
            'alamat' => 'Dusun Melati RT 05',
            'unit_id' => $unit->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseSelfReg->assertRedirect(route('nasabah.dashboard'));

        $selfNasabah = Nasabah::where('no_hp', $selfRegisterPhone)->first();
        $this->assertNotNull($selfNasabah);
        $this->assertNotNull($selfNasabah->no_rekening);
        $this->assertAuthenticatedAs($selfNasabah->user);

        // 5. Fitur Reset Password Nasabah ke Default oleh Admin/Petugas
        $responseReset = $this->actingAs($this->admin)->post(route('admin.nasabah.reset-password', $selfNasabah));
        $responseReset->assertRedirect();
        $responseReset->assertSessionHas('success');

        $expectedResetPassword = substr($selfRegisterPhone, -6);
        $this->post('/logout');
        $responseLoginReset = $this->post('/login', [
            'email' => $selfNasabah->no_rekening,
            'password' => $expectedResetPassword,
        ]);
        $responseLoginReset->assertRedirect(route('nasabah.dashboard'));
    }

    public function test_dual_pricing_structure_beli_and_jual(): void
    {
        $kategori = \App\Models\KategoriSampah::first() ?? \App\Models\KategoriSampah::create(['nama' => 'Plastik Test']);

        // 1. Simpan jenis sampah dengan 2 harga berbeda
        $responseStore = $this->actingAs($this->admin)->post(route('admin.jenis-sampah.store'), [
            'kategori_id' => $kategori->id,
            'nama' => 'Plastik Dual Price Test',
            'satuan' => 'kg',
            'harga_beli' => 2000,
            'harga_jual' => 3000,
            'deskripsi' => 'Testing dual price',
            'is_active' => '1',
        ]);
        $responseStore->assertRedirect(route('admin.jenis-sampah.index'));

        $sampah = \App\Models\JenisSampah::where('nama', 'Plastik Dual Price Test')->first();
        $this->assertNotNull($sampah);
        $this->assertEquals(2000, $sampah->harga_beli);
        $this->assertEquals(3000, $sampah->harga_jual);
        $this->assertEquals(2000, $sampah->harga_per_satuan); // backward compatibility
        $this->assertEquals(1000, $sampah->margin_rp);
        $this->assertEquals(50.0, $sampah->margin_persen);

        // 2. Update harga beli dan jual
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.jenis-sampah.update', $sampah), [
            'kategori_id' => $kategori->id,
            'nama' => 'Plastik Dual Price Test',
            'satuan' => 'kg',
            'harga_beli' => 2500,
            'harga_jual' => 3800,
            'is_active' => '1',
        ]);
        $responseUpdate->assertRedirect(route('admin.jenis-sampah.index'));

        $sampah->refresh();
        $this->assertEquals(2500, $sampah->harga_beli);
        $this->assertEquals(3800, $sampah->harga_jual);

        // 3. Pastikan log perubahan harga tercatat
        $latestLog = $sampah->hargaLogs()->first();
        $this->assertNotNull($latestLog);
        $this->assertEquals('keduanya', $latestLog->tipe_perubahan);
        $this->assertEquals(2000, $latestLog->harga_beli_lama);
        $this->assertEquals(2500, $latestLog->harga_beli_baru);
        $this->assertEquals(3000, $latestLog->harga_jual_lama);
        $this->assertEquals(3800, $latestLog->harga_jual_baru);

        // 4. Test satuan 'unit' dan 'buah'
        $responseUnit = $this->actingAs($this->admin)->post(route('admin.jenis-sampah.store'), [
            'kategori_id' => $kategori->id,
            'nama' => 'Elektronik Satuan Unit Test',
            'satuan' => 'unit',
            'harga_beli' => 15000,
            'harga_jual' => 20000,
            'is_active' => '1',
        ]);
        $responseUnit->assertRedirect(route('admin.jenis-sampah.index'));

        $responseBuah = $this->actingAs($this->admin)->post(route('admin.jenis-sampah.store'), [
            'kategori_id' => $kategori->id,
            'nama' => 'Kelapa Tua Satuan Buah Test',
            'satuan' => 'buah',
            'harga_beli' => 1000,
            'harga_jual' => 1500,
            'is_active' => '1',
        ]);
        $responseBuah->assertRedirect(route('admin.jenis-sampah.index'));

        $this->assertDatabaseHas('jenis_sampah', ['nama' => 'Elektronik Satuan Unit Test', 'satuan' => 'unit']);
        $this->assertDatabaseHas('jenis_sampah', ['nama' => 'Kelapa Tua Satuan Buah Test', 'satuan' => 'buah']);
    }

    public function test_unit_deletion_prevented_if_has_relations_and_succeeds_when_empty()
    {
        // 1. Buat unit yang berelasi dengan nasabah
        $unitWithRelation = \App\Models\Unit::create([
            'nama' => 'Unit Terisi Relasi',
            'kode_unit' => 'UNT-REL',
            'status' => 'aktif',
        ]);

        $testUser = User::create([
            'name' => 'Warga Unit Relasi',
            'email' => 'warga.relasi@banksampah.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'nasabah',
            'is_active' => true,
            'unit_id' => $unitWithRelation->id,
        ]);

        Nasabah::create([
            'user_id' => $testUser->id,
            'unit_id' => $unitWithRelation->id,
            'no_rekening' => 'BSB-TEST-REL',
            'nik' => '3201019988776655',
            'nama' => 'Warga Unit Relasi',
            'alamat' => 'Dusun Sukamaju RT 01',
            'no_hp' => '081234567899',
            'jenis_kelamin' => 'L',
            'saldo' => 0,
        ]);

        // Percobaan hapus unit yang memiliki nasabah -> Harus DITOLAK
        $responseFailed = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $unitWithRelation));
        $responseFailed->assertSessionHas('error');
        $this->assertDatabaseHas('units', ['id' => $unitWithRelation->id]);

        // 2. Buat unit kosong baru tanpa nasabah / transaksi / petugas
        $emptyUnit = \App\Models\Unit::create([
            'nama' => 'Unit Percobaan Kosong',
            'kode_unit' => 'UNT-KOSONG',
            'status' => 'aktif',
        ]);

        // Hapus unit kosong -> Harus BERHASIL
        $responseSuccess = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $emptyUnit));
        $responseSuccess->assertRedirect(route('admin.units.index'));
        $responseSuccess->assertSessionHas('success');
        $this->assertDatabaseMissing('units', ['id' => $emptyUnit->id]);
    }

    public function test_jenis_sampah_deletion_prevented_if_has_relations_and_succeeds_when_unused(): void
    {
        $kategori = \App\Models\KategoriSampah::first() ?? \App\Models\KategoriSampah::create([
            'nama' => 'Plastik Test',
            'deskripsi' => 'Kategori Plastik Test',
        ]);

        // 1. Buat jenis sampah dan hubungkan dengan transaksi setoran nasabah
        $sampahTerpakai = JenisSampah::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Botol Bening Terpakai',
            'satuan' => 'kg',
            'harga_beli' => 3000,
            'harga_jual' => 4500,
            'is_active' => true,
        ]);

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-TEST',
            'unit_id' => $this->nasabah->unit_id ?? \App\Models\Unit::first()->id,
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->petugas->id ?? $this->admin->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis' => 'setor',
            'total_berat' => 2.5,
            'total_nilai' => 7500,
            'status' => 'aktif',
        ]);

        \App\Models\DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'jenis_sampah_id' => $sampahTerpakai->id,
            'berat' => 2.5,
            'harga_satuan' => 3000,
            'subtotal' => 7500,
        ]);

        // Hapus sampah terpakai -> Harus GAGAL
        $responseFail = $this->actingAs($this->admin)->delete(route('admin.jenis-sampah.destroy', $sampahTerpakai));
        $responseFail->assertSessionHas('error');
        $this->assertDatabaseHas('jenis_sampah', ['id' => $sampahTerpakai->id]);

        // 2. Buat jenis sampah dan hubungkan dengan penjualan pengepul
        $pengepul = \App\Models\Pengepul::first() ?? \App\Models\Pengepul::create([
            'nama_perusahaan' => 'PT Daur Ulang Lestari',
            'penanggung_jawab' => 'Pak Hendra',
            'no_hp' => '089988776655',
            'alamat' => 'Gudang Pengepul Test',
            'status' => 'aktif',
        ]);

        $sampahPengepul = JenisSampah::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Kardus Terjual Pengepul',
            'satuan' => 'kg',
            'harga_beli' => 2000,
            'harga_jual' => 2500,
            'is_active' => true,
        ]);

        $penjualan = \App\Models\PenjualanSampah::create([
            'kode_penjualan' => 'PJL-' . time() . '-TEST',
            'pengepul_id' => $pengepul->id,
            'tanggal_penjualan' => now()->toDateString(),
            'total_berat' => 10,
            'total_pendapatan' => 25000,
            'status_pembayaran' => 'lunas',
            'pengelola_id' => $this->admin->id,
        ]);

        \App\Models\DetailPenjualanSampah::create([
            'penjualan_id' => $penjualan->id,
            'jenis_sampah_id' => $sampahPengepul->id,
            'berat' => 10,
            'harga_jual_satuan' => 2500,
            'subtotal' => 25000,
        ]);

        // Hapus sampah yang terjual ke pengepul -> Harus GAGAL
        $responseFailJual = $this->actingAs($this->admin)->delete(route('admin.jenis-sampah.destroy', $sampahPengepul));
        $responseFailJual->assertSessionHas('error');
        $this->assertDatabaseHas('jenis_sampah', ['id' => $sampahPengepul->id]);

        // 3. Buat jenis sampah bersih tanpa riwayat transaksi
        $sampahKosong = JenisSampah::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Sampah Uji Coba Bersih',
            'satuan' => 'unit',
            'harga_beli' => 5000,
            'harga_jual' => 8000,
            'is_active' => true,
        ]);

        // Non-admin (nasabah) mencoba menghapus -> Harus FORBIDDEN (403)
        $responseForbidden = $this->actingAs($this->nasabahUser)->delete(route('admin.jenis-sampah.destroy', $sampahKosong));
        $responseForbidden->assertForbidden();
        $this->assertDatabaseHas('jenis_sampah', ['id' => $sampahKosong->id]);

        // Admin menghapus jenis sampah kosong -> Harus BERHASIL
        $responseSuccess = $this->actingAs($this->admin)->delete(route('admin.jenis-sampah.destroy', $sampahKosong));
        $responseSuccess->assertRedirect(route('admin.jenis-sampah.index'));
        $responseSuccess->assertSessionHas('success');
        $this->assertDatabaseMissing('jenis_sampah', ['id' => $sampahKosong->id]);
    }

    public function test_petugas_can_register_and_manage_nasabah_in_their_unit(): void
    {
        $unitA = \App\Models\Unit::create(['nama' => 'Unit Mawar', 'kode_unit' => 'UNT-MWR', 'status' => 'aktif']);
        $unitB = \App\Models\Unit::create(['nama' => 'Unit Melati', 'kode_unit' => 'UNT-MLT', 'status' => 'aktif']);

        $petugasA = User::create([
            'name' => 'Petugas Mawar',
            'email' => 'petugas.mawar@banksampah.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'petugas',
            'is_active' => true,
            'unit_id' => $unitA->id,
        ]);

        // 1. Petugas bisa membuka form tambah nasabah
        $responseCreate = $this->actingAs($petugasA)->get(route('petugas.nasabah.create'));
        $responseCreate->assertStatus(200);

        // 2. Petugas menyimpan nasabah baru (unit_id otomatis terkunci ke unitA)
        $responseStore = $this->actingAs($petugasA)->post(route('petugas.nasabah.store'), [
            'nama' => 'Warga Mawar Baru',
            'nik' => '3201018899001122',
            'jenis_kelamin' => 'L',
            'no_hp' => '081234567888',
            'alamat' => 'Dusun Mawar No. 01',
            'status' => 'aktif',
        ]);

        $responseStore->assertRedirect(route('petugas.nasabah.index'));
        $responseStore->assertSessionHas('success');

        $nasabahBaru = Nasabah::where('nik', '3201018899001122')->first();
        $this->assertNotNull($nasabahBaru);
        $this->assertEquals($unitA->id, $nasabahBaru->unit_id);
        $this->assertEquals($petugasA->id, $nasabahBaru->registered_by);

        // 3. Petugas bisa melihat & mengedit nasabah di unitnya
        $responseShow = $this->actingAs($petugasA)->get(route('petugas.nasabah.show', $nasabahBaru));
        $responseShow->assertStatus(200);

        $responseEdit = $this->actingAs($petugasA)->get(route('petugas.nasabah.edit', $nasabahBaru));
        $responseEdit->assertStatus(200);

        // 4. Buat nasabah di unitB
        $nasabahUnitB = Nasabah::create([
            'user_id' => $this->nasabahUser->id,
            'unit_id' => $unitB->id,
            'no_rekening' => 'BS-TEST-B',
            'nik' => '3201015544332211',
            'nama' => 'Warga Melati',
            'jenis_kelamin' => 'P',
            'no_hp' => '081299887766',
            'alamat' => 'Dusun Melati',
            'saldo' => 0,
            'status' => 'aktif',
        ]);

        // Petugas unitA mencoba akses nasabah unitB -> 403 Forbidden
        $responseForbiddenShow = $this->actingAs($petugasA)->get(route('petugas.nasabah.show', $nasabahUnitB));
        $responseForbiddenShow->assertStatus(403);

        $responseForbiddenEdit = $this->actingAs($petugasA)->get(route('petugas.nasabah.edit', $nasabahUnitB));
        $responseForbiddenEdit->assertStatus(403);
    }

    public function test_petugas_can_access_cetak_struk_and_admin_struk_redirects(): void
    {
        $transaksi = Transaksi::first() ?? Transaksi::create([
            'kode_transaksi' => 'TRX-TEST-STRUK',
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->nasabah->unit_id,
            'petugas_id' => $this->petugas->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis' => 'setor',
            'total_berat' => 2.5,
            'total_nilai' => 25000,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 25000,
            'status' => 'aktif',
        ]);

        // 1. Petugas bisa membuka struk via route petugas
        $responsePetugas = $this->actingAs($this->petugas)->get(route('petugas.transaksi.struk', $transaksi));
        $responsePetugas->assertStatus(200);
        $responsePetugas->assertSee($transaksi->kode_transaksi);

        // 2. Petugas yang mengakses /admin/transaksi/{id}/struk diarahkan otomatis ke route petugas
        $responseAdminRedirect = $this->actingAs($this->petugas)->get("/admin/transaksi/{$transaksi->id}/struk");
        $responseAdminRedirect->assertRedirect("/petugas/transaksi/{$transaksi->id}/struk");

        // 3. Admin juga tetap bisa membuka struk
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.transaksi.struk', $transaksi));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee($transaksi->kode_transaksi);
    }

    public function test_pengelola_pin_authorization_for_penarikan_approval(): void
    {
        $pengelola = User::where('role', 'pengelola')->first() ?? User::create([
            'name' => 'Bendahara Desa',
            'email' => 'bendahara@banksampah.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'pengelola',
            'is_active' => true,
        ]);

        $pengelola->pin = \Illuminate\Support\Facades\Hash::make('654321');
        $pengelola->save();

        $this->nasabah->saldo = 100000;
        $this->nasabah->save();

        $trxMenunggu = Transaksi::create([
            'kode_transaksi' => 'TRX-TARIK-PIN-' . uniqid(),
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->nasabah->unit_id,
            'petugas_id' => $this->petugas->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis' => 'tarik',
            'total_berat' => 0,
            'total_nilai' => 30000,
            'saldo_sebelum' => 100000,
            'saldo_sesudah' => 70000,
            'status' => 'menunggu_persetujuan',
        ]);

        // 1. Pengelola salah memasukkan PIN -> Ditolak
        $responseWrongPin = $this->actingAs($pengelola)->post(route('admin.persetujuan-tarik.setujui', $trxMenunggu), [
            'pin' => '000000',
        ]);
        $responseWrongPin->assertRedirect();
        $responseWrongPin->assertSessionHas('error');
        $this->assertEquals('menunggu_persetujuan', $trxMenunggu->fresh()->status);

        // 2. Format PIN tidak valid (bukan 6 digit) -> Error validasi
        $responseInvalidPin = $this->actingAs($pengelola)->post(route('admin.persetujuan-tarik.setujui', $trxMenunggu), [
            'pin' => '123',
        ]);
        $responseInvalidPin->assertSessionHasErrors('pin');
        $this->assertEquals('menunggu_persetujuan', $trxMenunggu->fresh()->status);

        // 3. User tanpa PIN mencoba menyetujui -> Ditolak meminta setel PIN
        $userNoPin = User::create([
            'name' => 'Pengelola Tanpa PIN',
            'email' => 'nopin@banksampah.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'pengelola',
            'is_active' => true,
            'pin' => null,
        ]);
        $responseNoPin = $this->actingAs($userNoPin)->post(route('admin.persetujuan-tarik.setujui', $trxMenunggu), [
            'pin' => '123456',
        ]);
        $responseNoPin->assertSessionHas('error');
        $this->assertEquals('menunggu_persetujuan', $trxMenunggu->fresh()->status);

        // 4. Pengelola memasukkan PIN yang benar (654321) -> Berhasil disetujui & dicairkan
        $responseSuccess = $this->actingAs($pengelola)->post(route('admin.persetujuan-tarik.setujui', $trxMenunggu), [
            'pin' => '654321',
        ]);
        $responseSuccess->assertRedirect();
        $responseSuccess->assertSessionHas('success');

        $trxRefreshed = $trxMenunggu->fresh();
        $this->assertEquals('disetujui', $trxRefreshed->status);
        $this->assertEquals($pengelola->id, $trxRefreshed->disetujui_oleh);

        // 5. Pengelola dapat memperbarui PIN melalui route profil
        $responseUpdatePin = $this->actingAs($pengelola)->put(route('profile.pin.update'), [
            'current_password' => 'password',
            'pin' => '888999',
            'pin_confirmation' => '888999',
        ]);
        $responseUpdatePin->assertRedirect();
        $responseUpdatePin->assertSessionHas('success');
        $this->assertTrue($pengelola->fresh()->verifyPin('888999'));
    }

    public function test_penjualan_sampah_pelunasan_piutang(): void
    {
        $pengepul = \App\Models\Pengepul::first() ?? \App\Models\Pengepul::create([
            'nama_perusahaan' => 'CV Sumber Rejeki',
            'penanggung_jawab' => 'Pak Joko',
            'no_hp' => '081299998888',
            'status' => 'aktif',
        ]);

        $penjualan = \App\Models\PenjualanSampah::create([
            'kode_penjualan' => 'PJL-TEST-' . time(),
            'pengepul_id' => $pengepul->id,
            'pengelola_id' => $this->admin->id,
            'tanggal_penjualan' => now()->subDays(3)->toDateString(),
            'total_berat' => 150.0,
            'total_pendapatan' => 750000,
            'status_pembayaran' => 'piutang',
            'catatan' => 'Pengiriman tempo 3 hari',
        ]);

        $this->assertTrue($penjualan->isPiutang());
        $this->assertFalse($penjualan->isLunas());

        // 1. Cek halaman show menampilkan tombol pelunasan
        $responseShow = $this->actingAs($this->admin)->get(route('admin.penjualan.show', $penjualan));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Catat Pelunasan');

        // 2. Submit form pelunasan piutang
        $responsePelunasan = $this->actingAs($this->admin)->patch(route('admin.penjualan.pelunasan', $penjualan), [
            'tanggal_pelunasan' => now()->toDateString(),
            'metode_pelunasan' => 'transfer',
            'catatan_pelunasan' => 'Pelunasan transfer BCA No. Ref 123456789',
        ]);

        $responsePelunasan->assertRedirect(route('admin.penjualan.show', $penjualan));
        $responsePelunasan->assertSessionHas('success');

        $penjualanRefreshed = $penjualan->fresh();
        $this->assertEquals('lunas', $penjualanRefreshed->status_pembayaran);
        $this->assertEquals('transfer', $penjualanRefreshed->metode_pelunasan);
        $this->assertEquals('Pelunasan transfer BCA No. Ref 123456789', $penjualanRefreshed->catatan_pelunasan);
        $this->assertEquals($this->admin->id, $penjualanRefreshed->diterima_oleh);
        $this->assertTrue($penjualanRefreshed->isLunas());

        // 3. Pelunasan ulang harus ditolak karena sudah lunas
        $responseDouble = $this->actingAs($this->admin)->patch(route('admin.penjualan.pelunasan', $penjualanRefreshed), [
            'tanggal_pelunasan' => now()->toDateString(),
            'metode_pelunasan' => 'tunai',
        ]);
        $responseDouble->assertSessionHas('error');
    }
}
