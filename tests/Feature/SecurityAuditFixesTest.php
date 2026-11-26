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
}
