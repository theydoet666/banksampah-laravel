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
        'metode_penarikan',
        'petugas_id',
        'disetujui_oleh',
        'tanggal_persetujuan',
        'alasan_penolakan',
        'tanggal_transaksi',
    ];

    protected $casts = [
        'total_berat' => 'decimal:3',
        'total_nilai' => 'decimal:2',
        'tanggal_transaksi' => 'date',
        'tanggal_persetujuan' => 'datetime',
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

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
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
        return $query->whereIn('status', ['aktif', 'disetujui']);
    }

    public function scopeMenungguPersetujuan($query)
    {
        return $query->where('status', 'menunggu_persetujuan');
    }

    public function scopeDisetujui($query)
    {
        return $query->whereIn('status', ['aktif', 'disetujui']);
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'menunggu_persetujuan';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu_persetujuan' => 'Menunggu Persetujuan',
            'disetujui', 'aktif' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'void' => 'Dibatalkan (Void)',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'menunggu_persetujuan' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Menunggu</span>',
            'disetujui', 'aktif' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Disetujui</span>',
            'ditolak' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Ditolak</span>',
            'void' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 line-through">Void</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">' . e($this->status) . '</span>',
        };
    }
}

