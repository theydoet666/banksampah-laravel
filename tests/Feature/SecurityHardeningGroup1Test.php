<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Models\Transaksi;
use App\Models\KategoriSampah;
use App\Models\JenisSampah;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DomainException;

class SecurityHardeningGroup1Test extends TestCase
{
    use RefreshDatabase;

    protected Unit $unitA;
    protected Unit $unitB;
    protected User $petugasA;
    protected User $petugasB;
    protected User $petugasTanpaUnit;
    protected User $admin;
    protected Nasabah $nasabahA;
    protected Nasabah $nasabahB;
    protected JenisSampah $jenisSampah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitA = Unit::create(['kode_unit' => 'U-A', 'nama' => 'Unit Mawar', 'status' => 'aktif']);
        $this->unitB = Unit::create(['kode_unit' => 'U-B', 'nama' => 'Unit Melati', 'status' => 'aktif']);

        $this->admin = User::createWithRole([
            'name' => 'Super Admin',
            'email' => 'admin_test@banksampah.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ], 'admin');

        $this->petugasA = User::createWithRole([
            'name' => 'Petugas A',
            'email' => 'petugas_a@banksampah.test',
            'password' => bcrypt('password'),
            'unit_id' => $this->unitA->id,
            'is_active' => true,
        ], 'petugas');

        $this->petugasB = User::createWithRole([
            'name' => 'Petugas B',
            'email' => 'petugas_b@banksampah.test',
            'password' => bcrypt('password'),
            'unit_id' => $this->unitB->id,
            'is_active' => true,
        ], 'petugas');

        $this->petugasTanpaUnit = User::createWithRole([
            'name' => 'Petugas Tanpa Unit',
            'email' => 'petugas_no_unit@banksampah.test',
            'password' => bcrypt('password'),
            'unit_id' => null,
            'is_active' => true,
        ], 'petugas');

        $this->nasabahA = Nasabah::create([
            'unit_id' => $this->unitA->id,
            'no_rekening' => 'BS-2026-00001',
            'nama' => 'Nasabah Unit A',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat A',
            'no_hp' => '081111111111',
            'saldo' => 50000,
            'status' => 'aktif',
        ]);

        $this->nasabahB = Nasabah::create([
            'unit_id' => $this->unitB->id,
            'no_rekening' => 'BS-2026-00002',
            'nama' => 'Nasabah Unit B',
            'jenis_kelamin' => 'P',
            'alamat' => 'Alamat B',
            'no_hp' => '082222222222',
            'saldo' => 100000,
            'status' => 'aktif',
        ]);

        $kategori = KategoriSampah::create(['nama' => 'Plastik Test']);
        $this->jenisSampah = JenisSampah::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Botol PET Test',
            'satuan' => 'kg',
            'harga_beli' => 3000,
            'harga_jual' => 4000,
            'status' => 'aktif',
        ]);
    }

    public function test_petugas_a_cannot_access_nasabah_b_endpoints(): void
    {
        // 1. Show nasabah B
        $responseShow = $this->actingAs($this->petugasA)->get(route('petugas.nasabah.show', $this->nasabahB));
        $responseShow->assertStatus(403);

        // 2. Edit nasabah B
        $responseEdit = $this->actingAs($this->petugasA)->get(route('petugas.nasabah.edit', $this->nasabahB));
        $responseEdit->assertStatus(403);

        // 3. Update nasabah B
        $responseUpdate = $this->actingAs($this->petugasA)->put(route('petugas.nasabah.update', $this->nasabahB), [
            'nama' => 'Nama Bajakan',
            'jenis_kelamin' => 'P',
            'alamat' => 'Alamat Bajakan',
            'no_hp' => '089999999999',
            'status' => 'aktif',
        ]);
        $responseUpdate->assertStatus(403);

        // 4. Reset password nasabah B (T1)
        $responseReset = $this->actingAs($this->petugasA)->post(route('petugas.nasabah.reset-password', $this->nasabahB));
        $responseReset->assertStatus(403);
    }

    public function test_petugas_a_cannot_access_transaksi_nasabah_b(): void
    {
        // Buat transaksi di Unit B
        $transaksiB = Transaksi::create([
            'kode_transaksi' => 'TRX-B-00001',
            'nasabah_id' => $this->nasabahB->id,
            'unit_id' => $this->unitB->id,
            'jenis' => 'setor',
            'total_berat' => 10,
            'total_nilai' => 30000,
            'status' => 'aktif',
            'petugas_id' => $this->petugasB->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);

        // 1. Show transaksi B oleh Petugas A
        $responseShow = $this->actingAs($this->petugasA)->get(route('petugas.transaksi.show', $transaksiB));
        $responseShow->assertStatus(403);

        // 2. Struk transaksi B oleh Petugas A
        $responseStruk = $this->actingAs($this->petugasA)->get(route('petugas.transaksi.struk', $transaksiB));
        $responseStruk->assertStatus(403);

        // 3. Setor untuk nasabah B oleh Petugas A ditolak
        $responseSetor = $this->actingAs($this->petugasA)->post(route('petugas.transaksi.setor.store'), [
            'nasabah_id' => $this->nasabahB->id,
            'tanggal_transaksi' => now()->toDateString(),
            'details' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat' => 2],
            ],
        ]);
        $responseSetor->assertStatus(403);

        // 4. Tarik saldo untuk nasabah B oleh Petugas A ditolak
        $responseTarik = $this->actingAs($this->petugasA)->post(route('petugas.transaksi.tarik.store'), [
            'nasabah_id' => $this->nasabahB->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
        ]);
        $responseTarik->assertStatus(403);
    }

    public function test_petugas_without_unit_is_fail_closed(): void
    {
        // Petugas tanpa unit_id mencoba membuka halaman tambah nasabah
        $responseCreate = $this->actingAs($this->petugasTanpaUnit)->get(route('petugas.nasabah.create'));
        $responseCreate->assertStatus(403);

        // Petugas tanpa unit_id mencoba simpan nasabah
        $responseStore = $this->actingAs($this->petugasTanpaUnit)->post(route('petugas.nasabah.store'), [
            'nama' => 'Nasabah Ilegal',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat',
            'no_hp' => '081234567890',
            'status' => 'aktif',
        ]);
        $responseStore->assertStatus(403);

        // Petugas tanpa unit_id melihat list nasabah kosong
        $responseIndex = $this->actingAs($this->petugasTanpaUnit)->get(route('petugas.nasabah.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertDontSee($this->nasabahA->nama);
        $responseIndex->assertDontSee($this->nasabahB->nama);
    }

    public function test_double_approval_only_deducts_balance_once(): void
    {
        $service = app(TransaksiService::class);

        $transaksiTarik = Transaksi::create([
            'kode_transaksi' => 'TRX-TARIK-LOCK',
            'nasabah_id' => $this->nasabahA->id,
            'unit_id' => $this->unitA->id,
            'jenis' => 'tarik',
            'total_berat' => null,
            'total_nilai' => 20000,
            'status' => 'menunggu_persetujuan',
            'petugas_id' => $this->petugasA->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);

        // Approval pertama sukses
        $service->setujuiPenarikan($transaksiTarik, $this->admin);

        $this->nasabahA->refresh();
        $this->assertEquals(30000, $this->nasabahA->saldo);

        // Approval kedua harus melempar DomainException dan TIDAK memotong saldo lagi
        $this->expectException(DomainException::class);
        $service->setujuiPenarikan($transaksiTarik, $this->admin);

        $this->nasabahA->refresh();
        $this->assertEquals(30000, $this->nasabahA->saldo);
    }

    public function test_double_void_only_reverts_balance_once(): void
    {
        $service = app(TransaksiService::class);

        $transaksiSetor = Transaksi::create([
            'kode_transaksi' => 'TRX-SETOR-VOID',
            'nasabah_id' => $this->nasabahA->id,
            'unit_id' => $this->unitA->id,
            'jenis' => 'setor',
            'total_berat' => 5,
            'total_nilai' => 15000,
            'status' => 'aktif',
            'petugas_id' => $this->petugasA->id,
            'tanggal_transaksi' => now()->toDateString(),
        ]);
        // Saldo awal 50.000 + 15.000 = 65.000
        $this->nasabahA->update(['saldo' => 65000]);

        // Void pertama sukses
        $service->voidTransaksi($transaksiSetor, 'Uji coba void');

        $this->nasabahA->refresh();
        $this->assertEquals(50000, $this->nasabahA->saldo);

        // Void kedua harus melempar DomainException dan TIDAK memotong saldo lagi
        $this->expectException(DomainException::class);
        $service->voidTransaksi($transaksiSetor, 'Uji coba void kedua');

        $this->nasabahA->refresh();
        $this->assertEquals(50000, $this->nasabahA->saldo);
    }

    public function test_nonaktif_nasabah_cannot_transact(): void
    {
        $service = app(TransaksiService::class);
        $this->nasabahA->update(['status' => 'nonaktif']);

        $this->expectException(DomainException::class);
        $service->prosesSetoran([
            'nasabah_id' => $this->nasabahA->id,
            'tanggal_transaksi' => now()->toDateString(),
            'details' => [
                ['jenis_sampah_id' => $this->jenisSampah->id, 'berat' => 1],
            ],
        ]);
    }
}
