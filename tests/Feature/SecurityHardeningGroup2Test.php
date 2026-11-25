<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Services\PinGuard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class SecurityHardeningGroup2Test extends TestCase
{
    use DatabaseTransactions;

    protected Unit $unit;
    protected User $admin;
    protected User $petugas;
    protected Nasabah $nasabah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create(['kode_unit' => 'U-SEC2', 'nama' => 'Unit Security 2', 'status' => 'aktif']);

        $this->admin = User::createWithRole([
            'name' => 'Admin Security 2',
            'email' => 'admin_sec2@banksampah.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ], 'admin');
        $this->admin->setPin('654321');

        $this->petugas = User::createWithRole([
            'name' => 'Petugas Security 2',
            'email' => 'petugas_sec2@banksampah.test',
            'password' => Hash::make('password'),
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ], 'petugas');

        $this->nasabah = Nasabah::create([
            'no_rekening' => 'BS-SEC2-00001',
            'nama' => 'Nasabah Sec2',
            'nik' => '3201000000000002',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Sec2',
            'no_hp' => '089999999992',
            'unit_id' => $this->unit->id,
            'status' => 'aktif',
            'saldo' => 50000,
            'saldo_awal' => 50000,
            'registered_by' => $this->admin->id,
        ]);

        RateLimiter::clear('pin_check:' . $this->admin->id);
    }

    public function test_admin_tarik_langsung_tanpa_pin_gagal()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
            'metode_penarikan' => 'tunai',
        ]);

        $response->assertSessionHasErrors(['pin']);
    }

    public function test_admin_tarik_langsung_dengan_pin_salah_gagal()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabah->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
            'metode_penarikan' => 'tunai',
            'pin' => '000000',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_pin_terkunci_setelah_5_kali_salah()
    {
        RateLimiter::clear('pin_check:' . $this->admin->id);

        for ($i = 1; $i <= 5; $i++) {
            $result = PinGuard::check($this->admin, '000000');
            $this->assertNotNull($result);
        }

        // Percobaan ke-6 walaupun PIN benar harus tetap ditolak karena akun terkunci
        $lockedResult = PinGuard::check($this->admin, '654321');
        $this->assertNotNull($lockedResult);
        $this->assertStringContainsString('terkunci', $lockedResult);
    }

    public function test_password_awal_nasabah_bukan_6_digit_hp_dan_memaksa_ganti_password()
    {
        $uniquePhone = '081234567899';
        $response = $this->actingAs($this->admin)->post(route('admin.nasabah.store'), [
            'nama' => 'Nasabah Baru Sec2',
            'nik' => '3201999988887777',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Uji Keamanan',
            'no_hp' => $uniquePhone,
            'status' => 'aktif',
            'unit_id' => $this->unit->id,
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');
        $genPassword = session('generated_password');

        $this->assertEquals(10, strlen($genPassword));
        $this->assertNotEquals(substr($uniquePhone, -6), $genPassword);

        $newNasabah = Nasabah::where('no_hp', $uniquePhone)->first();
        $this->assertNotNull($newNasabah);
        $this->assertNotNull($newNasabah->user);
        $this->assertTrue((bool)$newNasabah->user->must_change_password);
        $this->assertTrue(Hash::check($genPassword, $newNasabah->user->password));
    }

    public function test_validasi_unique_no_hp_pada_nasabah()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.nasabah.store'), [
            'nama' => 'Nasabah Duplikat HP',
            'nik' => '3201111122223333',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Duplikat',
            'no_hp' => $this->nasabah->no_hp, // sudah ada di setUp
            'status' => 'aktif',
            'unit_id' => $this->unit->id,
        ]);

        $response->assertSessionHasErrors(['no_hp']);
    }

    public function test_update_pin_wajibkan_current_password()
    {
        $userWithoutPin = User::factory()->create([
            'role' => 'admin',
            'pin' => null,
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->actingAs($userWithoutPin)->put(route('profile.pin.update'), [
            'pin' => '112233',
            'pin_confirmation' => '112233',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }
}
