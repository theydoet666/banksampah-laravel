<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Exceptions\InsufficientBalanceException;
use App\Support\MoneyMath;
use Illuminate\Support\Facades\DB;
use DomainException;
use Exception;

class TransaksiService
{
    public function prosesSetoran(array $data): Transaksi
    {
        return DB::transaction(function () use ($data) {
            $nasabah = Nasabah::lockForUpdate()->findOrFail($data['nasabah_id']);
            if ($nasabah->status !== 'aktif') {
                throw new DomainException('Nasabah berstatus nonaktif tidak dapat melakukan transaksi setoran.');
            }

            $totalNilai = '0.00';
            $totalBerat = 0;

            // Hitung total dari item sampah menggunakan BCMath untuk presisi eksak
            foreach ($data['details'] as $item) {
                $jenisSampah = JenisSampah::findOrFail($item['jenis_sampah_id']);
                $subtotal = MoneyMath::mul($item['berat'], $jenisSampah->harga_per_satuan);
                $totalNilai = MoneyMath::add($totalNilai, $subtotal);
                $totalBerat += (float) $item['berat'];
            }

            $totalNilai = MoneyMath::toFloat($totalNilai);
            $totalBerat = round($totalBerat, 3);
            $unitId = $nasabah->unit_id ?? auth()->user()?->unit_id;

            $transaksi = Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
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
                $subtotal = MoneyMath::toFloat(MoneyMath::mul($item['berat'], $jenisSampah->harga_per_satuan));

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'jenis_sampah_id' => $jenisSampah->id,
                    'berat' => $item['berat'],
                    'harga_satuan' => $jenisSampah->harga_per_satuan, // snapshot harga
                    'subtotal' => $subtotal,
                ]);
            }

            // Tambah saldo nasabah di dalam lock
            $nasabah->increment('saldo', $totalNilai);

            auditLog(
                action: 'create',
                module: 'Transaksi Warga',
                description: "Mencatat setoran sampah warga {$nasabah->nama} ({$transaksi->kode_transaksi}) senilai " . formatRupiah($totalNilai) . " (" . number_format($totalBerat, 2) . " kg)",
                subject: $transaksi
            );

            return $transaksi;
        });
    }

    /**
     * Pengajuan penarikan saldo oleh nasabah atau petugas unit
     */
    public function ajukanPenarikan(array $data, ?\App\Models\User $actor = null): Transaksi
    {
        return DB::transaction(function () use ($data, $actor) {
            $nasabah = Nasabah::lockForUpdate()->findOrFail($data['nasabah_id']);
            if ($nasabah->status !== 'aktif') {
                throw new DomainException('Nasabah berstatus nonaktif tidak dapat mengajukan penarikan saldo.');
            }

            $nominal = round((float) $data['nominal'], 2);

            if ($nasabah->saldo_tersedia < $nominal) {
                throw new InsufficientBalanceException('Saldo tersedia nasabah tidak mencukupi untuk nominal pengajuan ini (tersedia: ' . $nasabah->formatted_saldo_tersedia . ').');
            }

            $user = $actor ?? auth()->user();
            $unitId = $nasabah->unit_id ?? $user?->unit_id;

            $transaksi = Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
                'unit_id' => $unitId,
                'jenis' => 'tarik',
                'total_berat' => null,
                'total_nilai' => $nominal,
                'catatan' => $data['catatan'] ?? null,
                'metode_penarikan' => $data['metode_penarikan'] ?? 'tunai',
                'status' => 'menunggu_persetujuan',
                'petugas_id' => $user?->id ?? $nasabah->user_id,
                'tanggal_transaksi' => $data['tanggal_transaksi'] ?? now()->toDateString(),
            ]);

            auditLog(
                action: 'create',
                module: 'Transaksi Warga',
                description: "Mengajukan penarikan saldo nasabah {$nasabah->nama} ({$transaksi->kode_transaksi}) senilai " . formatRupiah($nominal),
                subject: $transaksi,
                user: $user
            );

            return $transaksi;
        });
    }

    /**
     * Konfirmasi & persetujuan penarikan oleh Pengelola Desa (Admin)
     */
    public function setujuiPenarikan(Transaksi $transaksi, \App\Models\User $admin): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $admin) {
            // Lock transaksi dan baca ulang status terkini
            $lockedTrx = Transaksi::lockForUpdate()->findOrFail($transaksi->id);

            if ($lockedTrx->status !== 'menunggu_persetujuan' || $lockedTrx->jenis !== 'tarik') {
                throw new DomainException('Transaksi ini tidak sedang menunggu persetujuan penarikan.');
            }

            // Lock nasabah dan baca ulang saldo terkini
            $nasabah = Nasabah::lockForUpdate()->findOrFail($lockedTrx->nasabah_id);
            if ($nasabah->status !== 'aktif') {
                throw new DomainException('Nasabah berstatus nonaktif tidak dapat mencairkan saldo.');
            }

            if ($nasabah->saldo < $lockedTrx->total_nilai) {
                throw new InsufficientBalanceException('Gagal menyetujui: Saldo nasabah saat ini tidak mencukupi.');
            }

            // Kurangi saldo nasabah secara resmi
            $nasabah->decrement('saldo', $lockedTrx->total_nilai);

            $lockedTrx->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $admin->id,
                'tanggal_persetujuan' => now(),
            ]);

            auditLog(
                action: 'approve',
                module: 'Persetujuan Tarik',
                description: "Menyetujui pencairan penarikan saldo {$lockedTrx->kode_transaksi} nasabah {$nasabah->nama} senilai " . formatRupiah($lockedTrx->total_nilai) . " dengan verifikasi PIN",
                subject: $lockedTrx,
                user: $admin
            );

            return $lockedTrx->fresh();
        });
    }

    /**
     * Penolakan penarikan oleh Pengelola Desa (Admin)
     */
    public function tolakPenarikan(Transaksi $transaksi, \App\Models\User $admin, string $alasan): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $admin, $alasan) {
            $lockedTrx = Transaksi::lockForUpdate()->findOrFail($transaksi->id);

            if ($lockedTrx->status !== 'menunggu_persetujuan' || $lockedTrx->jenis !== 'tarik') {
                throw new DomainException('Transaksi ini tidak sedang menunggu persetujuan penarikan.');
            }

            $lockedTrx->update([
                'status' => 'ditolak',
                'disetujui_oleh' => $admin->id,
                'tanggal_persetujuan' => now(),
                'alasan_penolakan' => $alasan,
            ]);

            auditLog(
                action: 'reject',
                module: 'Persetujuan Tarik',
                description: "Menolak pengajuan penarikan saldo {$lockedTrx->kode_transaksi} nasabah {$lockedTrx->nasabah->nama}. Alasan: {$alasan}",
                subject: $lockedTrx,
                user: $admin
            );

            return $lockedTrx->fresh();
        });
    }

    public function prosesPenarikan(array $data): Transaksi
    {
        $user = auth()->user();

        // Jika diajukan oleh petugas unit / nasabah, alurnya adalah pengajuan (menunggu persetujuan desa)
        if ($user && $user->isPetugas()) {
            return $this->ajukanPenarikan($data, $user);
        }

        // Jika admin desa sendiri yang mencatat, penarikan langsung disetujui & dicairkan
        return DB::transaction(function () use ($data, $user) {
            $nasabah = Nasabah::lockForUpdate()->findOrFail($data['nasabah_id']);
            if ($nasabah->status !== 'aktif') {
                throw new DomainException('Nasabah berstatus nonaktif tidak dapat melakukan penarikan saldo.');
            }

            $nominal = round((float) $data['nominal'], 2);

            if ($nasabah->saldo < $nominal) {
                throw new InsufficientBalanceException('Saldo nasabah tidak mencukupi untuk penarikan ini.');
            }

            $unitId = $nasabah->unit_id ?? $user?->unit_id;

            $transaksi = Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'nasabah_id' => $nasabah->id,
                'unit_id' => $unitId,
                'jenis' => 'tarik',
                'total_berat' => null,
                'total_nilai' => $nominal,
                'catatan' => $data['catatan'] ?? null,
                'metode_penarikan' => $data['metode_penarikan'] ?? 'tunai',
                'status' => 'disetujui',
                'petugas_id' => $user?->id ?? auth()->id(),
                'disetujui_oleh' => $user?->id ?? auth()->id(),
                'tanggal_persetujuan' => now(),
                'tanggal_transaksi' => $data['tanggal_transaksi'] ?? now()->toDateString(),
            ]);

            // Kurangi saldo nasabah di dalam lock
            $nasabah->decrement('saldo', $nominal);

            auditLog(
                action: 'create',
                module: 'Transaksi Warga',
                description: "Mencairkan langsung penarikan saldo nasabah {$nasabah->nama} ({$transaksi->kode_transaksi}) senilai " . formatRupiah($nominal),
                subject: $transaksi,
                user: $user
            );

            return $transaksi;
        });
    }

    public function voidTransaksi(Transaksi $transaksi, ?string $alasan = null): void
    {
        DB::transaction(function () use ($transaksi, $alasan) {
            $lockedTrx = Transaksi::lockForUpdate()->findOrFail($transaksi->id);

            if ($lockedTrx->status === 'void') {
                throw new DomainException('Transaksi sudah dibatalkan sebelumnya.');
            }

            $nasabah = Nasabah::lockForUpdate()->findOrFail($lockedTrx->nasabah_id);

            if ($lockedTrx->jenis === 'setor') {
                if ($nasabah->saldo < $lockedTrx->total_nilai) {
                    throw new DomainException('Gagal void: Saldo nasabah sudah berkurang dan lebih kecil dari nilai setoran ini.');
                }
                $nasabah->decrement('saldo', $lockedTrx->total_nilai);
            } else {
                // Tarik: hanya kembalikan saldo jika transaksi sebelumnya sudah disetujui/aktif (saldo sudah terpotong)
                if (in_array($lockedTrx->status, ['aktif', 'disetujui'])) {
                    $nasabah->increment('saldo', $lockedTrx->total_nilai);
                }
            }

            $desc = "Membatalkan (void) transaksi {$lockedTrx->kode_transaksi} nasabah {$nasabah->nama} senilai " . formatRupiah($lockedTrx->total_nilai);
            if (! empty($alasan)) {
                $desc .= ". Alasan: {$alasan}";
            }

            $lockedTrx->update([
                'status' => 'void',
                'alasan_penolakan' => $alasan ?? $lockedTrx->alasan_penolakan,
            ]);

            auditLog(
                action: 'void',
                module: 'Transaksi Warga',
                description: $desc,
                subject: $lockedTrx
            );
        });
    }
}
