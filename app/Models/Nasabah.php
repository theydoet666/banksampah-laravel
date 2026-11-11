<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nasabah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nasabah';

    protected $fillable = [
        'user_id',
        'unit_id',
        'no_rekening',
        'nama',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'foto',
        'saldo',
        'status',
        'registered_by',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'tanggal_lahir' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function petugasPendaftar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'nasabah_id');
    }

    public static function generateNoRekening(): string
    {
        $year = date('Y');
        $lastNasabah = self::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastNasabah && preg_match('/(\d+)$/', $lastNasabah->no_rekening, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('BS-%s-%05d', $year, $sequence);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function getFormattedSaldoAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->saldo, 0, ',', '.');
    }

    public function getSaldoTertahanAttribute(): float
    {
        return (float) $this->transaksi()
            ->where('jenis', 'tarik')
            ->where('status', 'menunggu_persetujuan')
            ->sum('total_nilai');
    }

    public function getSaldoTersediaAttribute(): float
    {
        return max(0, (float) $this->saldo - $this->saldo_tertahan);
    }

    public function getFormattedSaldoTertahanAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo_tertahan, 0, ',', '.');
    }

    public function getFormattedSaldoTersediaAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo_tersedia, 0, ',', '.');
    }
}

