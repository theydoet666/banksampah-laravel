<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenjualanSampah extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan_sampah';

    protected $fillable = [
        'penjualan_id',
        'jenis_sampah_id',
        'berat',
        'harga_jual_satuan',
        'subtotal',
    ];

    protected $casts = [
        'berat' => 'decimal:3',
        'harga_jual_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(PenjualanSampah::class, 'penjualan_id');
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }
}

