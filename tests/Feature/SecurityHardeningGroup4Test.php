<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Support\SpreadsheetSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SecurityHardeningGroup4Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Unit $unit;
    protected Nasabah $nasabah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create(['kode_unit' => 'U-SEC4', 'nama' => 'Unit Security 4', 'status' => 'aktif']);

        $this->admin = User::createWithRole([
            'name' => 'Admin Security 4',
            'email' => 'admin_sec4@banksampah.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ], 'admin');
        $this->admin->setPin('123456');

        $this->nasabah = Nasabah::create([
            'no_rekening' => 'BS-SEC4-00001',
            'nama' => 'Nasabah Sec4',
            'nik' => '3201000000000004',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Sec4',
            'no_hp' => '089999999994',
            'unit_id' => $this->unit->id,
            'status' => 'aktif',
            'saldo' => 10000,
            'saldo_awal' => 0,
            'registered_by' => $this->admin->id,
        ]);
    }

    public function test_svg_upload_ditolak_pada_pengaturan()
    {
        Storage::fake('public');

        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="5"/></svg>';
        $svgFile = UploadedFile::fake()->createWithContent('logo.svg', $svgContent);

        $response = $this->actingAs($this->admin)->put(route('admin.pengaturan.update'), [
            'nama_bank_sampah' => 'Bank Sampah BAA',
            'alamat' => 'Jl. Uji No. 1',
            'no_telp' => '08123456789',
            'email' => 'info@banksampah.id',
            'logo' => $svgFile,
        ]);

        $response->assertSessionHasErrors(['logo']);
    }

    public function test_spreadsheet_sanitizer_prefixes_formula_characters()
    {
        $this->assertEquals("'=SUM(A1:A10)", SpreadsheetSanitizer::sanitize('=SUM(A1:A10)'));
        $this->assertEquals("'+12345", SpreadsheetSanitizer::sanitize('+12345'));
        $this->assertEquals("'-100", SpreadsheetSanitizer::sanitize('-100'));
        $this->assertEquals("'@cmd", SpreadsheetSanitizer::sanitize('@cmd'));
        $this->assertEquals("Normal Text", SpreadsheetSanitizer::sanitize('Normal Text'));
        $this->assertEquals(12345, SpreadsheetSanitizer::sanitize(12345));
    }

    public function test_admin_terakhir_tidak_bisa_hapus_akun()
    {
        // Hanya ada 1 admin aktif ($this->admin)
        $response = $this->actingAs($this->admin)->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', ['userDeletion']);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_nasabah_dengan_saldo_tidak_bisa_hapus_akun()
    {
        $nasabahUser = User::createWithRole([
            'name' => 'Warga Bersaldo',
            'email' => 'warga.saldo@banksampah.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ], 'nasabah');

        $this->nasabah->user_id = $nasabahUser->id;
        $this->nasabah->save();

        $response = $this->actingAs($nasabahUser)->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrorsIn('userDeletion', ['userDeletion']);
        $this->assertDatabaseHas('users', ['id' => $nasabahUser->id]);
    }

    public function test_input_saldo_awal_memerlukan_pin_dan_mencegah_duplikasi()
    {
        // 1. Tanpa PIN -> Error validasi PIN
        $responseNoPin = $this->actingAs($this->admin)->post(route('admin.nasabah.saldo-awal', $this->nasabah), [
            'nominal' => 50000,
            'tanggal_transaksi' => now()->toDateString(),
        ]);
        $responseNoPin->assertSessionHasErrors(['pin']);

        // 2. Dengan PIN valid -> Berhasil
        $responseSuccess = $this->actingAs($this->admin)->post(route('admin.nasabah.saldo-awal', $this->nasabah), [
            'nominal' => 50000,
            'tanggal_transaksi' => now()->toDateString(),
            'pin' => '123456',
        ]);
        $responseSuccess->assertSessionHas('success');

        // 3. Coba input kedua kali tanpa force -> Ditolak 422
        $responseDuplicate = $this->actingAs($this->admin)->post(route('admin.nasabah.saldo-awal', $this->nasabah), [
            'nominal' => 20000,
            'tanggal_transaksi' => now()->toDateString(),
            'pin' => '123456',
        ]);
        $responseDuplicate->assertStatus(422);
    }
}
