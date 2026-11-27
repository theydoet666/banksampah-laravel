<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Models\KategoriSampah;
use App\Models\JenisSampah;
use App\Models\Pengepul;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\AuditLog;
use App\Services\PenjualanService;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use DomainException;

class SecurityAuditFixesTest extends TestCase
{
    use DatabaseTransactions;

    protected Unit $unit;
    protected User $admin;
    protected User $pengelola;
    protected Nasabah $nasabah;
    protected User $nasabahUser;
    protected JenisSampah $jenisSampah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create([
            'kode_unit' => 'U-AUD1',
            'nama' => 'Unit Audit 1',
            'status' => 'aktif',
        ]);

        $this->admin = User::createWithRole([
            'name' => 'Admin Audit',
            'email' => 'admin_audit_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ], 'admin');

        $this->pengelola = User::createWithRole([
            'name' => 'Pengelola Audit',
            'email' => 'pengelola_audit_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ], 'pengelola');

        $this->nasabahUser = User::createWithRole([
            'name' => 'Nasabah Audit',
            'email' => 'nasabah_audit_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ], 'nasabah');

        $this->nasabah = Nasabah::create([
            'user_id' => $this->nasabahUser->id,
            'unit_id' => $this->unit->id,
            'no_rekening' => 'BS-AUD-' . rand(10000, 99999),
            'nama' => 'Nasabah Audit',
            'no_hp' => '0899' . rand(10000000, 99999999),
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Audit',
            'status' => 'aktif',
            'saldo' => 100000,
            'saldo_awal' => 0,
            'registered_by' => $this->admin->id,
        ]);

        $kategori = KategoriSampah::create(['nama' => 'Kategori Audit ' . uniqid()]);
        $this->jenisSampah = JenisSampah::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Plastik Audit ' . uniqid(),
            'satuan' => 'kg',
            'harga_beli' => 2000,
            'harga_jual' => 3000,
            'harga_per_satuan' => 2000,
            'is_active' => true,
        ]);
    }

    public function test_penjualan_rejects_duplicate_jenis_sampah_rows_exceeding_stock(): void
    {
        $this->actingAs($this->admin);

        // Stok masuk tepat 10 kg untuk jenis sampah ini
        $trx = Transaksi::create([
            'kode_transaksi' => Transaksi::generateKodeTransaksi(),
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->unit->id,
            'jenis' => 'setor',
            'total_berat' => 10,
            'total_nilai' => 20000,
            'status' => 'aktif',
            'petugas_id' => $this->admin->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);

        DetailTransaksi::create([
            'transaksi_id' => $trx->id,
            'jenis_sampah_id' => $this->jenisSampah->id,
            'berat' => 10,
            'harga_satuan' => 2000,
            'subtotal' => 20000,
        ]);

        $pengepul = Pengepul::create([
            'nama_perusahaan' => 'PT Audit Pengepul',
            'status' => 'aktif',
        ]);

        $service = app(PenjualanService::class);
        $sisaStok = $service->getSisaStok($this->jenisSampah->id);

        // Kirim dua baris dengan jenis_sampah_id sama yang masing-masing <= sisaStok, tapi totalnya > sisaStok
        $halfPlus = round(($sisaStok / 2) + 1, 2);

        $this->expectException(DomainException::class);
        $service->simpanPenjualan([
            'pengepul_id' => $pengepul->id,
            'tanggal_penjualan' => now()->toDateString(),
            'status_pembayaran' => 'lunas',
            'details' => [
                [
                    'jenis_sampah_id' => $this->jenisSampah->id,
                    'berat' => $halfPlus,
                    'harga_jual_satuan' => 3000,
                ],
                [
                    'jenis_sampah_id' => $this->jenisSampah->id,
                    'berat' => $halfPlus,
                    'harga_jual_satuan' => 3000,
                ],
            ],
        ]);
    }

    public function test_toggle_status_nasabah_syncs_user_is_active(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.nasabah.toggle-status', $this->nasabah));

        $response->assertRedirect();
        $this->assertEquals('nonaktif', $this->nasabah->fresh()->status);
        $this->assertFalse((bool) $this->nasabahUser->fresh()->is_active);
    }

    public function test_void_setor_blocked_when_saldo_tersedia_insufficient_due_to_pending_tarik(): void
    {
        $this->actingAs($this->admin);

        $setor = Transaksi::create([
            'kode_transaksi' => Transaksi::generateKodeTransaksi(),
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->unit->id,
            'jenis' => 'setor',
            'total_berat' => 10,
            'total_nilai' => 50000,
            'status' => 'aktif',
            'petugas_id' => $this->admin->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);

        // Pengajuan tarik tertahan 80.000 dari saldo 100.000 -> saldo_tersedia = 20.000
        Transaksi::create([
            'kode_transaksi' => Transaksi::generateKodeTransaksi(),
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->unit->id,
            'jenis' => 'tarik',
            'total_nilai' => 80000,
            'status' => 'menunggu_persetujuan',
            'petugas_id' => $this->nasabahUser->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);

        $this->expectException(DomainException::class);
        app(TransaksiService::class)->voidTransaksi($setor, 'Tes saldo tertahan');
    }

    public function test_user_controller_update_blocks_demoting_last_active_admin_and_reset_password_uses_safe_flash(): void
    {
        // Pastikan hanya admin test ini yang aktif sementara dalam transaksi
        User::where('role', 'admin')->where('id', '!=', $this->admin->id)->update(['is_active' => false]);

        $demoteResp = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->admin), [
                'name' => $this->admin->name,
                'email' => $this->admin->email,
                'role' => 'petugas',
                'unit_id' => $this->unit->id,
                'is_active' => '1',
            ]);

        $demoteResp->assertSessionHas('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);

        $resetResp = $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $this->pengelola));

        $resetResp->assertSessionHas('generated_password');
        $this->assertStringNotContainsString('<strong>', (string) session('success'));
        $this->assertTrue(
            AuditLog::where('action', 'reset_password')
                ->where('module', 'Pengguna Sistem')
                ->where('subject_id', $this->pengelola->id)
                ->exists()
        );
    }

    public function test_pengelola_store_tarik_redirects_to_admin_route_not_403(): void
    {
        $response = $this->actingAs($this->pengelola)
            ->post(route('admin.transaksi.tarik.store'), [
                'nasabah_id' => $this->nasabah->id,
                'nominal' => 10000,
                'tanggal_transaksi' => now()->toDateString(),
                'pin' => '123456',
            ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/admin/transaksi/', (string) $location);
        $this->assertStringNotContainsString('/petugas/transaksi/', (string) $location);
    }

    public function test_login_throttle_key_normalizes_phone_format(): void
    {
        \Illuminate\Support\Facades\RateLimiter::clear('081234567890|127.0.0.1');
        \Illuminate\Support\Facades\RateLimiter::clear('login_ip:127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => '0812-3456-7890',
                'password' => 'wrong-pass',
            ]);
        }

        // Format spasi untuk nomor yang sama harus terkena throttle bucket yang sama
        $resp = $this->post('/login', [
            'email' => '0812 3456 7890',
            'password' => 'wrong-pass',
        ]);

        $resp->assertSessionHasErrors('email');
        $this->assertTrue(\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('081234567890|127.0.0.1', 5));
        $this->assertNotEquals(trans('auth.failed'), session('errors')->first('email'));
    }

    public function test_nasabah_foto_stored_privately_and_enforces_unit_authorization(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        \Illuminate\Support\Facades\Storage::disk('local')->put('nasabah/test-private.jpg', 'fake-image-bytes');
        $this->nasabah->update(['foto' => 'nasabah/test-private.jpg']);

        $otherUnit = Unit::create(['kode_unit' => 'U-AUD2', 'nama' => 'Unit Audit 2', 'status' => 'aktif']);
        $otherPetugas = User::createWithRole([
            'name' => 'Petugas Unit Lain',
            'email' => 'petugas_lain_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'unit_id' => $otherUnit->id,
            'is_active' => true,
        ], 'petugas');

        $this->actingAs($otherPetugas)
            ->get(route('petugas.nasabah.foto', $this->nasabah))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('admin.nasabah.foto', $this->nasabah))
            ->assertStatus(200);
    }

    public function test_laba_rugi_rejects_invalid_date_input(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.laporan.laba-rugi'))
            ->get(route('admin.laporan.laba-rugi', [
                'tanggal_dari' => 'bukan-tanggal',
                'tanggal_sampai' => '2026-01-01',
            ]));

        $response->assertRedirect(route('admin.laporan.laba-rugi'));
        $response->assertSessionHasErrors(['tanggal_dari']);
    }

    public function test_audit_log_export_streams_csv_safely(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.audit-log.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_security_headers_include_csp(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
    }

    public function test_petugas_can_delete_zero_balance_nasabah_only_in_own_unit_and_admin_can_delete_any_unit(): void
    {
        $unitLain = Unit::create(['kode_unit' => 'U-DEL2', 'nama' => 'Unit Delete 2', 'status' => 'aktif']);

        $petugasUnit1 = User::createWithRole([
            'name' => 'Petugas Unit 1',
            'email' => 'petugas_del1_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ], 'petugas');

        $petugasTanpaUnit = User::createWithRole([
            'name' => 'Petugas Tanpa Unit',
            'email' => 'petugas_del0_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'unit_id' => null,
            'is_active' => true,
        ], 'petugas');

        $nasabahUnit1 = Nasabah::create([
            'unit_id' => $this->unit->id,
            'no_rekening' => 'BS-DEL-' . rand(10000, 99999),
            'nama' => 'Nasabah Hapus 1',
            'no_hp' => '0888' . rand(10000000, 99999999),
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test',
            'status' => 'aktif',
            'saldo' => 0,
            'registered_by' => $this->admin->id,
        ]);

        $nasabahUnit2 = Nasabah::create([
            'unit_id' => $unitLain->id,
            'no_rekening' => 'BS-DEL-' . rand(10000, 99999),
            'nama' => 'Nasabah Hapus 2',
            'no_hp' => '0887' . rand(10000000, 99999999),
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Test 2',
            'status' => 'aktif',
            'saldo' => 0,
            'registered_by' => $this->admin->id,
        ]);

        // 1. Petugas tanpa unit -> 403
        $this->actingAs($petugasTanpaUnit)
            ->delete(route('petugas.nasabah.destroy', $nasabahUnit1))
            ->assertStatus(403);

        // 2. Petugas unit 1 mencoba hapus nasabah unit 2 -> 403
        $this->actingAs($petugasUnit1)
            ->delete(route('petugas.nasabah.destroy', $nasabahUnit2))
            ->assertStatus(403);

        // 3. Pengelola mencoba hapus nasabah -> 403
        $this->actingAs($this->pengelola)
            ->delete(route('admin.nasabah.destroy', $nasabahUnit1))
            ->assertStatus(403);

        // 4. Petugas unit 1 menghapus nasabah unit 1 (saldo 0, tanpa transaksi) -> sukses
        $this->actingAs($petugasUnit1)
            ->delete(route('petugas.nasabah.destroy', $nasabahUnit1))
            ->assertRedirect(route('petugas.nasabah.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('nasabah', ['id' => $nasabahUnit1->id]);

        // 5. Admin menghapus nasabah dari unit mana pun (unit 2) -> sukses
        $this->actingAs($this->admin)
            ->delete(route('admin.nasabah.destroy', $nasabahUnit2))
            ->assertRedirect(route('admin.nasabah.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('nasabah', ['id' => $nasabahUnit2->id]);

        // 6. Nasabah dengan saldo > 0 ditolak hapus
        $this->actingAs($this->admin)
            ->delete(route('admin.nasabah.destroy', $this->nasabah))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('nasabah', ['id' => $this->nasabah->id]);
    }

    public function test_new_nasabah_uses_belega_email_domain(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.nasabah.store'), [
            'nama' => 'Nasabah Domain Baru',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Belega',
            'no_hp' => '0855' . rand(10000000, 99999999),
            'unit_id' => $this->unit->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.nasabah.index'));
        $created = Nasabah::where('nama', 'Nasabah Domain Baru')->firstOrFail();
        $this->assertStringEndsWith('@nasabah.banksampah.belega.id', $created->user->email);
    }

    public function test_stok_gudang_page_paginates_jenis_sampah_with_accurate_totals(): void
    {
        $kategori = $this->jenisSampah->kategori;
        for ($i = 1; $i <= 16; $i++) {
            JenisSampah::create([
                'kategori_id' => $kategori->id,
                'nama' => "Sampah Paging {$i}",
                'satuan' => 'kg',
                'harga_beli' => 1000,
                'harga_jual' => 1500,
                'harga_per_satuan' => 1000,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.penjualan.stok'));
        $response->assertStatus(200);
        $response->assertViewHas('stokGudang', function ($paginator) {
            return $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
                && $paginator->perPage() === 15
                && $paginator->total() >= 17
                && count($paginator->items()) === 15;
        });

        $page2 = $this->actingAs($this->admin)->get(route('admin.penjualan.stok', ['page' => 2]));
        $page2->assertStatus(200);
    }

    public function test_pengelola_without_pin_sees_current_password_field_and_can_create_and_use_pin(): void
    {
        $pengelolaBaru = User::createWithRole([
            'name' => 'Pengelola Baru',
            'email' => 'pengelola_nopin_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'pin' => null,
            'is_active' => true,
        ], 'pengelola');

        $this->assertFalse($pengelolaBaru->hasPin());

        // 1. Halaman profil tetap menampilkan field current_password meskipun belum punya PIN
        $profileResp = $this->actingAs($pengelolaBaru)->get(route('profile.edit'));
        $profileResp->assertStatus(200);
        $profileResp->assertSee('pin_current_password', false);

        // 2. Pengelola membuat PIN baru dengan current_password
        $saveResp = $this->actingAs($pengelolaBaru)->put(route('profile.pin.update'), [
            'current_password' => 'password123',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ]);
        $saveResp->assertRedirect();
        $saveResp->assertSessionHas('status', 'pin-updated');

        $pengelolaBaru->refresh();
        $this->assertTrue($pengelolaBaru->hasPin());
        $this->assertTrue($pengelolaBaru->verifyPin('654321'));

        // 3. PIN baru langsung bisa dipakai untuk transaksi tarik
        $tarikResp = $this->actingAs($pengelolaBaru)->post(route('admin.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
            'pin' => '654321',
        ]);
        $tarikResp->assertRedirect();
        $tarikResp->assertSessionHas('success');
    }

    public function test_opsi1_temp_password_encrypted_auto_wiped_and_admin_export_requires_pin(): void
    {
        $petugas = User::createWithRole([
            'name' => 'Petugas Export Test',
            'email' => 'petugas_exp_' . uniqid() . '@banksampah.test',
            'password' => Hash::make('password123'),
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ], 'petugas');

        // 1. Buat nasabah baru -> temp_password_encrypted tersimpan & hidden dari JSON
        $noHp = '0856' . rand(10000000, 99999999);
        $createResp = $this->actingAs($this->admin)->post(route('admin.nasabah.store'), [
            'nama' => 'Nasabah Opsi1',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Opsi 1',
            'no_hp' => $noHp,
            'unit_id' => $this->unit->id,
            'status' => 'aktif',
        ]);
        $createResp->assertSessionHas('generated_password');
        $plainPass = session('generated_password');

        $nasabahBaru = Nasabah::where('no_hp', $noHp)->firstOrFail();
        $userBaru = $nasabahBaru->user;
        $this->assertNotEmpty($userBaru->temp_password_encrypted);
        $this->assertEquals($plainPass, \Illuminate\Support\Facades\Crypt::decryptString($userBaru->temp_password_encrypted));
        $this->assertArrayNotHasKey('temp_password_encrypted', $userBaru->toArray());

        // 2. Non-admin ditolak export (403)
        $this->actingAs($petugas)
            ->post(route('admin.nasabah.export-credentials'), ['pin' => '123456'])
            ->assertStatus(403);

        // 3. Admin dengan PIN salah ditolak
        $this->actingAs($this->admin)
            ->post(route('admin.nasabah.export-credentials'), ['pin' => '000000'])
            ->assertSessionHas('error');

        // 4. Admin dengan PIN benar berhasil export .xlsx
        $exportResp = $this->actingAs($this->admin)
            ->post(route('admin.nasabah.export-credentials'), [
                'pin' => '123456',
                'unit_id' => $this->unit->id,
                'only_pending' => '1',
            ]);
        $exportResp->assertStatus(200);
        $this->assertStringContainsString('.xlsx', (string) $exportResp->headers->get('Content-Disposition'));

        // 5. Setelah nasabah ganti password saat login pertama, temp_password_encrypted otomatis terhapus (NULL)
        $this->actingAs($userBaru)
            ->post(route('password.first_login.update'), [
                'current_password' => $plainPass,
                'password' => 'PasswordBaru123!',
                'password_confirmation' => 'PasswordBaru123!',
            ])
            ->assertRedirect(route('dashboard'));

        $userBaru->refresh();
        $this->assertFalse((bool) $userBaru->must_change_password);
        $this->assertNull($userBaru->temp_password_encrypted);

        $mapped = (new \App\Exports\NasabahCredentialsExport(collect([$nasabahBaru->fresh(['user', 'unit'])])))->map($nasabahBaru->fresh(['user', 'unit']));
        $this->assertEquals('Sudah diganti oleh nasabah', $mapped[6]);
    }

    public function test_opsi3_nasabah_self_activation_with_nik_works_once_and_rejects_invalid_or_already_active(): void
    {
        $nik = '510401' . rand(1000000000, 9999999999);
        $noHp = '0877' . rand(10000000, 99999999);

        $userPending = User::createWithRole([
            'name' => 'Nasabah Aktivasi',
            'email' => 'aktivasi_' . uniqid() . '@nasabah.banksampah.belega.id',
            'password' => Hash::make('TempPass99'),
            'unit_id' => $this->unit->id,
            'is_active' => true,
            'must_change_password' => true,
        ], 'nasabah');
        $userPending->forceFill(['temp_password_encrypted' => \Illuminate\Support\Facades\Crypt::encryptString('TempPass99')])->save();

        $nasabahPending = Nasabah::create([
            'user_id' => $userPending->id,
            'unit_id' => $this->unit->id,
            'no_rekening' => 'BS-ACT-' . rand(10000, 99999),
            'nama' => 'Nasabah Aktivasi',
            'nik' => $nik,
            'no_hp' => $noHp,
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Aktivasi',
            'status' => 'aktif',
            'saldo' => 0,
            'registered_by' => $this->admin->id,
        ]);

        // 1. Halaman aktivasi bisa diakses tamu
        $this->get(route('nasabah.activate'))->assertStatus(200);

        // 2. NIK salah ditolak
        $this->post(route('nasabah.activate.store'), [
            'no_rekening' => $nasabahPending->no_rekening,
            'nik' => '9999999999999999',
            'no_hp' => $noHp,
            'password' => 'SandiPribadi99',
            'password_confirmation' => 'SandiPribadi99',
        ])->assertSessionHasErrors('no_rekening');
        $this->assertGuest();

        // 3. No. Rekening + NIK + No. HP cocok -> berhasil aktivasi & login
        $okResp = $this->post(route('nasabah.activate.store'), [
            'no_rekening' => $nasabahPending->no_rekening,
            'nik' => $nik,
            'no_hp' => $noHp,
            'password' => 'SandiPribadi99',
            'password_confirmation' => 'SandiPribadi99',
        ]);
        $okResp->assertRedirect(route('nasabah.dashboard'));
        $this->assertAuthenticatedAs($userPending);

        $userPending->refresh();
        $this->assertFalse((bool) $userPending->must_change_password);
        $this->assertNull($userPending->temp_password_encrypted);
        $this->assertTrue(Hash::check('SandiPribadi99', $userPending->password));

        // 4. Logout dan coba aktivasi ulang akun yang sudah aktif -> ditolak (sekali pakai)
        $this->post('/logout');
        $this->post(route('nasabah.activate.store'), [
            'no_rekening' => $nasabahPending->no_rekening,
            'nik' => $nik,
            'no_hp' => $noHp,
            'password' => 'AmbilAlih99',
            'password_confirmation' => 'AmbilAlih99',
        ])->assertSessionHasErrors('no_rekening');
        $this->assertGuest();
    }
}
