<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'nasabah_id',
        'unit_id',
        'jenis',
        'total_berat',
        'total_nilai',
        'catatan',
        'status',
        'petugas_id',
        'tanggal_transaksi',
    ];

    protected $casts = [
        'total_berat' => 'decimal:3',
        'total_nilai' => 'decimal:2',
        'tanggal_transaksi' => 'date',
    ];

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }

    public static function generateKodeTransaksi(): string
    {
        $date = date('Ymd');
        $lastTrx = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastTrx && preg_match('/(\d+)$/', $lastTrx->kode_transaksi, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('TRX-%s-%05d', $date, $sequence);
    }

    public function scopeSetor($query)
    {
        return $query->where('jenis', 'setor');
    }

    public function scopeTarik($query)
    {
        return $query->where('jenis', 'tarik');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}

