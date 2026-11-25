<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\User;

trait AuthorizesUnitAccess
{
    /**
     * Pastikan petugas hanya dapat mengakses data nasabah di unit miliknya.
     * Fail-closed: Petugas tanpa unit_id ditolak secara otomatis.
     */
    protected function authorizeUnitAccessForNasabah(Nasabah $nasabah, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if ($user && $user->isPetugas()) {
            if (empty($user->unit_id)) {
                abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
            }

            if ((int) $nasabah->unit_id !== (int) $user->unit_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang pada nasabah di unit lain.');
            }
        }
    }

    /**
     * Pastikan petugas hanya dapat mengakses transaksi di unit miliknya.
     * Fail-closed: Petugas tanpa unit_id ditolak secara otomatis.
     */
    protected function authorizeUnitAccessForTransaksi(Transaksi $transaksi, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if ($user && $user->isPetugas()) {
            if (empty($user->unit_id)) {
                abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
            }

            if ((int) $transaksi->unit_id !== (int) $user->unit_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang pada transaksi di unit lain.');
            }
        }
    }

    /**
     * Batasi query Eloquent untuk petugas agar hanya melihat data unitnya sendiri.
     * Fail-closed: Petugas tanpa unit_id diarahkan ke query kosong (unit_id = 0).
     */
    protected function scopeUnitQuery($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        if ($user && $user->isPetugas()) {
            if (empty($user->unit_id)) {
                // Petugas tanpa unit tidak boleh melihat data apapun
                return $query->where('unit_id', 0);
            }

            return $query->where('unit_id', $user->unit_id);
        }

        return $query;
    }
}
