<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;
use Exception;

class TransaksiService
{
    public function prosesSetoran(array $data): Transaksi
    {
        return DB::transaction(function () use ($data) {
            $totalNilai = 0;
            $totalBerat = 0;

            // Hitung total dari item sampah
            foreach ($data['details'] as $item) {
                $jenisSampah = JenisSampah::findOrFail($item['jenis_sampah_id']);
                $subtotal = (float) $item['berat'] * (float) $jenisSampah->harga_per_satuan;
                $totalNilai += $subtotal;
                $totalBerat += (float) $item['berat'];
            }

            $nasabah = Nasabah::findOrFail($data['nasabah_id']);
            $unitId = $nasabah->unit_id ?? auth()->user()?->unit_id;

            $transaksi = Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'nasabah_id' => $data['nasabah_id'],
                'unit_id' => $unitId,
                'jenis' => 'setor',
                'total_berat' => $totalBerat,
                'total_nilai' => $totalNilai,
                'catatan' => $data['catatan'] ?? null,
                'status' => 'aktif',
                'petugas_id' => auth()->id(),
                'tanggal_transaksi' => $data['tanggal_transaksi'] ?? now()->toDateString(),
            ]);

            foreach ($data['details'] as $item) {
                $jenisSampah = JenisSampah::findOrFail($item['jenis_sampah_id']);
                $subtotal = (float) $item['berat'] * (float) $jenisSampah->harga_per_satuan;

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'jenis_sampah_id' => $jenisSampah->id,
                    'berat' => $item['berat'],
                    'harga_satuan' => $jenisSampah->harga_per_satuan, // snapshot harga
                    'subtotal' => $subtotal,
                ]);
            }

            // Tambah saldo nasabah
            $nasabah = Nasabah::findOrFail($data['nasabah_id']);
            $nasabah->increment('saldo', $totalNilai);

            return $transaksi;
        });
    }

    public function prosesPenarikan(array $data): Transaksi
    {
        return DB::transaction(function () use ($data) {
            $nasabah = Nasabah::findOrFail($data['nasabah_id']);
            $nominal = (float) $data['nominal'];

            if ($nasabah->saldo < $nominal) {
                throw new InsufficientBalanceException('Saldo nasabah tidak mencukupi untuk penarikan ini.');
            }

            $unitId = $nasabah->unit_id ?? auth()->user()?->unit_id;

            $transaksi = Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
                'unit_id' => $unitId,
                'jenis' => 'tarik',
                'total_berat' => null,
                'total_nilai' => $nominal,
                'catatan' => $data['catatan'] ?? null,
                'status' => 'aktif',
                'petugas_id' => auth()->id(),
                'tanggal_transaksi' => $data['tanggal_transaksi'] ?? now()->toDateString(),
            ]);

            // Kurangi saldo nasabah
            $nasabah->decrement('saldo', $nominal);

            return $transaksi;
        });
    }

    public function voidTransaksi(Transaksi $transaksi): void
    {
        DB::transaction(function () use ($transaksi) {
            if ($transaksi->status === 'void') {
                throw new Exception('Transaksi sudah dibatalkan sebelumnya.');
            }

            $nasabah = $transaksi->nasabah;

            if ($transaksi->jenis === 'setor') {
                if ($nasabah->saldo < $transaksi->total_nilai) {
                    throw new Exception('Gagal void: Saldo nasabah sudah berkurang dan lebih kecil dari nilai setoran ini.');
                }
                $nasabah->decrement('saldo', $transaksi->total_nilai);
            } else {
                // Tarik dibatalkan -> kembalikan saldo
                $nasabah->increment('saldo', $transaksi->total_nilai);
            }

            $transaksi->update(['status' => 'void']);
        });
    }
}

