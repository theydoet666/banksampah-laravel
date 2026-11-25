<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Models\Transaksi;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class SecurityHardeningGroup5Test extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Unit $unit;
    protected Nasabah $nasabah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create(['kode_unit' => 'U-SEC5', 'nama' => 'Unit Security 5', 'status' => 'aktif']);

        $this->admin = User::createWithRole([
            'name' => 'Admin Security 5',
            'email' => 'admin_sec5@banksampah.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ], 'admin');
        $this->admin->setPin('123456');

        $this->nasabah = Nasabah::create([
            'no_rekening' => 'BS-SEC5-00001',
            'nama' => 'Nasabah Sec5',
            'nik' => '3201000000000005',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Sec5',
            'no_hp' => '089999999995',
            'unit_id' => $this->unit->id,
            'status' => 'aktif',
            'saldo' => 50000,
            'saldo_awal' => 0,
            'registered_by' => $this->admin->id,
        ]);
    }

    public function test_security_headers_are_present()
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_void_mencatat_alasan_pembatalan()
    {
        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-VOID-ALASAN',
            'nasabah_id' => $this->nasabah->id,
            'unit_id' => $this->unit->id,
            'petugas_id' => $this->admin->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis' => 'setor',
            'total_berat' => 1.0,
            'total_nilai' => 10000,
            'status' => 'aktif',
        ]);

        $this->nasabah->increment('saldo', 10000);

        $response = $this->actingAs($this->admin)->post(route('admin.transaksi.void', $transaksi), [
            'alasan' => 'Nasabah salah membawa jenis sampah',
        ]);

        $response->assertSessionHas('warning');
        $transaksi->refresh();
        $this->assertEquals('void', $transaksi->status);
        $this->assertEquals('Nasabah salah membawa jenis sampah', $transaksi->alasan_penolakan);

        $audit = AuditLog::where('action', 'void')
            ->where('subject_id', $transaksi->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertStringContainsString('Nasabah salah membawa jenis sampah', $audit->description);
    }

    public function test_reset_dummy_data_command_ditolak_di_production_tanpa_force()
    {
        // Set environment production sementara
        app()->detectEnvironment(fn () => 'production');

        $this->artisan('bank-sampah:reset-data')
            ->assertExitCode(1);

        app()->detectEnvironment(fn () => 'testing');
    }
}
