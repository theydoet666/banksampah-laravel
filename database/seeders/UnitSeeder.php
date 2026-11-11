<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Transaksi;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'kode_unit' => 'U-01',
                'nama' => 'Unit Melati (Dusun 1)',
                'alamat' => 'Balai Dusun 1, Jl. Melati No. 12',
                'nama_ketua' => 'Ibu Rahmawati',
                'no_hp' => '081234567801',
                'status' => 'aktif',
            ],
            [
                'kode_unit' => 'U-02',
                'nama' => 'Unit Mawar (Dusun 2)',
                'alamat' => 'Posyandu Mawar, RT 03/RW 02',
                'nama_ketua' => 'Bpk. Haryanto',
                'no_hp' => '081234567802',
                'status' => 'aktif',
            ],
            [
                'kode_unit' => 'U-03',
                'nama' => 'Unit Kenanga (RW 05)',
                'alamat' => 'Pos RW 05, Jl. Kenanga Raya',
                'nama_ketua' => 'Ibu Nurhayati',
                'no_hp' => '081234567803',
                'status' => 'aktif',
            ],
        ];

        $createdUnits = [];
        foreach ($units as $u) {
            $createdUnits[] = Unit::firstOrCreate(['kode_unit' => $u['kode_unit']], $u);
        }

        // Tugaskan Petugas Satu ke Unit 1 (Melati)
        $petugas = User::where('role', 'petugas')->first();
        if ($petugas && !empty($createdUnits)) {
            $petugas->unit_id = $createdUnits[0]->id;
            $petugas->save();
        }

        // Tautkan nasabah demo yang belum punya unit_id ke unit-unit ini
        $allNasabah = Nasabah::whereNull('unit_id')->get();
        if ($allNasabah->isNotEmpty()) {
            foreach ($allNasabah as $idx => $nasabah) {
                $assignedUnit = $createdUnits[$idx % count($createdUnits)];
                $nasabah->unit_id = $assignedUnit->id;
                $nasabah->save();
            }
        }

        // Tautkan transaksi ke unit_id nasabahnya
        $transaksiList = Transaksi::whereNull('unit_id')->with('nasabah')->get();
        foreach ($transaksiList as $trx) {
            if ($trx->nasabah && $trx->nasabah->unit_id) {
                $trx->unit_id = $trx->nasabah->unit_id;
                $trx->save();
            }
        }
    }
}
