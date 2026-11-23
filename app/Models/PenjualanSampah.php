<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenjualanSampah extends Model
{
    use HasFactory;

    protected $table = 'penjualan_sampah';

    protected $fillable = [
        'kode_penjualan',
        'pengepul_id',
        'tanggal_penjualan',
        'total_berat',
        'total_pendapatan',
        'status_pembayaran',
        'tanggal_pelunasan',
        'metode_pelunasan',
        'catatan_pelunasan',
        'diterima_oleh',
        'pengelola_id',
        'catatan',
    ];

    protected $casts = [
        'total_berat' => 'decimal:3',
        'total_pendapatan' => 'decimal:2',
        'tanggal_penjualan' => 'date',
        'tanggal_pelunasan' => 'date',
    ];

    public function pengepul(): BelongsTo
    {
        return $this->belongsTo(Pengepul::class, 'pengepul_id');
    }

    public function pengelola(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengelola_id');
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    public function isPiutang(): bool
    {
        return $this->status_pembayaran === 'piutang';
    }

    public function isLunas(): bool
    {
        return $this->status_pembayaran === 'lunas';
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPenjualanSampah::class, 'penjualan_id');
    }

    public static function generateKodePenjualan(): string
    {
        $date = date('Ymd');
        $lastTrx = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastTrx && preg_match('/(\d+)$/', $lastTrx->kode_penjualan, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('JUAL-%s-%05d', $date, $sequence);
    }
}

