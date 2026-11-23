<?php

namespace App\Services;

use App\Models\PenjualanSampah;
use App\Models\DetailPenjualanSampah;
use App\Models\JenisSampah;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Exception;

class PenjualanService
{
    /**
     * Hitung stok fisik sampah di gudang desa untuk semua jenis sampah.
     */
    public function getStokSampahGudang()
    {
        $jenisSampahList = JenisSampah::with('kategori')->orderBy('kategori_id')->orderBy('nama')->get();

        // Hitung total berat masuk dari transaksi setoran nasabah yang aktif
        $masukPerJenis = DetailTransaksi::whereHas('transaksi', function ($q) {
            $q->where('status', 'aktif')->where('jenis', 'setor');
        })
        ->select('jenis_sampah_id', DB::raw('SUM(berat) as total_masuk'))
        ->groupBy('jenis_sampah_id')
        ->pluck('total_masuk', 'jenis_sampah_id')
        ->toArray();

        // Hitung total berat keluar dari transaksi penjualan ke pengepul
        $terjualPerJenis = DetailPenjualanSampah::select('jenis_sampah_id', DB::raw('SUM(berat) as total_terjual'))
            ->groupBy('jenis_sampah_id')
            ->pluck('total_terjual', 'jenis_sampah_id')
            ->toArray();

        $stokSummary = [];

        foreach ($jenisSampahList as $jenis) {
            $masuk = (float) ($masukPerJenis[$jenis->id] ?? 0);
            $terjual = (float) ($terjualPerJenis[$jenis->id] ?? 0);
            $sisa = max(0, $masuk - $terjual);

            $hargaBeli = (float) ($jenis->harga_beli ?? $jenis->harga_per_satuan ?? 0);
            $hargaJual = (float) ($jenis->harga_jual ?? round($hargaBeli * 1.30, -1));
            $estimasiNilaiBeli = $sisa * $hargaBeli;
            $estimasiNilaiJual = $sisa * $hargaJual;

            $stokSummary[] = [
                'id' => $jenis->id,
                'nama' => $jenis->nama,
                'kategori' => $jenis->kategori->nama ?? '-',
                'satuan' => $jenis->satuan,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'total_masuk' => $masuk,
                'total_terjual' => $terjual,
                'sisa_stok' => $sisa,
                'estimasi_nilai' => $estimasiNilaiBeli,
                'estimasi_nilai_beli' => $estimasiNilaiBeli,
                'estimasi_nilai_jual' => $estimasiNilaiJual,
                'potensi_laba' => max(0, $estimasiNilaiJual - $estimasiNilaiBeli),
            ];
        }

        return collect($stokSummary);
    }

    /**
     * Ambil sisa stok untuk satu jenis sampah.
     */
    public function getSisaStok(int $jenisSampahId): float
    {
        $masuk = (float) DetailTransaksi::where('jenis_sampah_id', $jenisSampahId)
            ->whereHas('transaksi', function ($q) {
                $q->where('status', 'aktif')->where('jenis', 'setor');
            })->sum('berat');

        $terjual = (float) DetailPenjualanSampah::where('jenis_sampah_id', $jenisSampahId)->sum('berat');

        return max(0, $masuk - $terjual);
    }

    /**
     * Simpan transaksi penjualan sampah ke pengepul dengan validasi stok.
     */
    public function simpanPenjualan(array $data): PenjualanSampah
    {
        return DB::transaction(function () use ($data) {
            $totalBerat = 0;
            $totalPendapatan = 0;

            // Validasi stok fisik gudang desa untuk setiap item
            foreach ($data['details'] as $item) {
                $jenisSampah = JenisSampah::findOrFail($item['jenis_sampah_id']);
                $beratJual = (float) $item['berat'];
                $sisaStok = $this->getSisaStok($jenisSampah->id);

                if ($beratJual > $sisaStok) {
                    throw new Exception("Stok untuk '{$jenisSampah->nama}' tidak mencukupi. Sisa stok di gudang: {$sisaStok} kg, diminta: {$beratJual} kg.");
                }

                $hargaJual = (float) $item['harga_jual_satuan'];
                $subtotal = $beratJual * $hargaJual;

                $totalBerat += $beratJual;
                $totalPendapatan += $subtotal;
            }

            // Simpan header penjualan
            $penjualan = PenjualanSampah::create([
                'kode_penjualan' => PenjualanSampah::generateKodePenjualan(),
                'pengepul_id' => $data['pengepul_id'],
                'tanggal_penjualan' => $data['tanggal_penjualan'] ?? now()->toDateString(),
                'total_berat' => $totalBerat,
                'total_pendapatan' => $totalPendapatan,
                'status_pembayaran' => $data['status_pembayaran'] ?? 'lunas',
                'pengelola_id' => auth()->id(),
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Simpan detail penjualan
            foreach ($data['details'] as $item) {
                $beratJual = (float) $item['berat'];
                $hargaJual = (float) $item['harga_jual_satuan'];
                $subtotal = $beratJual * $hargaJual;

                DetailPenjualanSampah::create([
                    'penjualan_id' => $penjualan->id,
                    'jenis_sampah_id' => $item['jenis_sampah_id'],
                    'berat' => $beratJual,
                    'harga_jual_satuan' => $hargaJual,
                    'subtotal' => $subtotal,
                ]);
            }

            auditLog(
                action: 'create',
                module: 'Gudang & Penjualan',
                description: "Mencatat penjualan sampah {$penjualan->kode_penjualan} ke pengepul {$penjualan->pengepul->nama_perusahaan} senilai " . formatRupiah($totalPendapatan) . " (" . number_format($totalBerat, 2) . " kg) [Status: " . ucfirst($penjualan->status_pembayaran) . "]",
                subject: $penjualan
            );

            return $penjualan;
        });
    }

    /**
     * Catat pelunasan pembayaran piutang penjualan sampah dari pengepul.
     */
    public function catatPelunasan(PenjualanSampah $penjualan, array $data, $user): PenjualanSampah
    {
        if ($penjualan->status_pembayaran === 'lunas') {
            throw new Exception("Transaksi penjualan [{$penjualan->kode_penjualan}] sudah berstatus Lunas.");
        }

        $penjualan->update([
            'status_pembayaran' => 'lunas',
            'tanggal_pelunasan' => $data['tanggal_pelunasan'] ?? now()->toDateString(),
            'metode_pelunasan' => $data['metode_pelunasan'] ?? 'transfer',
            'catatan_pelunasan' => $data['catatan_pelunasan'] ?? null,
            'diterima_oleh' => $user->id,
        ]);

        auditLog(
            action: 'pelunasan',
            module: 'Gudang & Penjualan',
            description: "Mencatat pelunasan piutang penjualan {$penjualan->kode_penjualan} dari {$penjualan->pengepul->nama_perusahaan} sebesar " . formatRupiah($penjualan->total_pendapatan) . " via {$penjualan->metode_pelunasan}",
            subject: $penjualan,
            user: $user
        );

        return $penjualan;
    }
}

