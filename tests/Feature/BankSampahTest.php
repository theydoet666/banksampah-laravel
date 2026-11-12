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

    protected User $admin;
    protected User $petugas;
    protected User $nasabahUser;
    protected Nasabah $nasabah;

    protected function setUp(): void
    {
        parent::setUp();

        if (!User::where('role', 'admin')->exists()) {
            $this->artisan('db:seed');
        }

        $this->admin = User::where('role', 'admin')->first();
        $this->petugas = User::where('role', 'petugas')->first();
        $this->nasabahUser = User::where('role', 'nasabah')->first();
        $this->nasabah = Nasabah::where('user_id', $this->nasabahUser?->id)->first() ?? Nasabah::where('status', 'aktif')->first();
        if ($this->nasabah && $this->nasabahUser && $this->nasabah->user_id !== $this->nasabahUser->id) {
            $this->nasabah->update(['user_id' => $this->nasabahUser->id]);
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
            'email' => 'nasabah@banksampah.test',
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
        $pengepul = \App\Models\Pengepul::first();
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

        $response = $this->actingAs($this->admin)->put(route('admin.pengaturan.update'), [
            'nama_bank_sampah' => 'Bank Sampah Berkah Mandiri',
            'tagline' => 'Bersih Lingkunganku, Sejahtera Wargaku',
            'no_telp' => '081234567890',
            'email' => 'admin@berkahmandiri.test',
            'alamat' => 'Jl. Hijau Lestari No. 10',
            'tahun_berdiri' => '2024',
            'logo' => $logoFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Bank Sampah Berkah Mandiri', \App\Models\AppSetting::get('nama_bank_sampah'));
        $this->assertEquals('Bersih Lingkunganku, Sejahtera Wargaku', \App\Models\AppSetting::get('tagline'));
        
        $logoPath = \App\Models\AppSetting::get('logo');
        $this->assertNotEmpty($logoPath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($logoPath);
    }

    public function test_nasabah_pengajuan_tarik_and_admin_approval_workflow(): void
    {
        // 1. Pastikan nasabah punya saldo
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

        // 5. Admin menyetujui penarikan
        $responseApprove = $this->actingAs($this->admin)->post(route('admin.persetujuan-tarik.setujui', $transaksiPengajuan));
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
}
