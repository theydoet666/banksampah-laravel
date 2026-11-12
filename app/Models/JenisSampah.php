<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';

    protected $fillable = [
        'kategori_id',
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'harga_per_satuan',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_per_satuan' => 'decimal:2',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_id');
    }

    public function hargaLogs(): HasMany
    {
        return $this->hasMany(HargaSampahLog::class, 'jenis_sampah_id')->orderByDesc('id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'jenis_sampah_id');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualanSampah::class, 'jenis_sampah_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Backward-compatibility: harga_per_satuan selalu mengacu ke harga_beli.
     */
    public function getHargaPerSatuanAttribute($value)
    {
        return $this->attributes['harga_beli'] ?? $value ?? 0;
    }

    public function setHargaBeliAttribute($value)
    {
        $this->attributes['harga_beli'] = $value;
        $this->attributes['harga_per_satuan'] = $value;
    }

    /**
     * Selisih margin nominal (Harga Jual - Harga Beli)
     */
    public function getMarginRpAttribute(): float
    {
        $beli = (float) ($this->attributes['harga_beli'] ?? $this->attributes['harga_per_satuan'] ?? 0);
        $jual = (float) ($this->attributes['harga_jual'] ?? 0);
        return max(0, $jual - $beli);
    }

    /**
     * Margin persentase keuntungan desa
     */
    public function getMarginPersenAttribute(): float
    {
        $beli = (float) ($this->attributes['harga_beli'] ?? $this->attributes['harga_per_satuan'] ?? 0);
        $jual = (float) ($this->attributes['harga_jual'] ?? 0);

        if ($beli <= 0) {
            return 0;
        }

        return round((($jual - $beli) / $beli) * 100, 1);
    }
}
