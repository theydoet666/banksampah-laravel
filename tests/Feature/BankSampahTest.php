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

        $this->admin = User::where('role', 'admin')->first();
        $this->petugas = User::where('role', 'petugas')->first();
        $this->nasabahUser = User::where('role', 'nasabah')->first();
        $this->nasabah = Nasabah::where('status', 'aktif')->first();
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

        $response = $this->actingAs($this->petugas)->post(route('petugas.transaksi.tarik.store'), [
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
}
