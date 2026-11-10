<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaSampahLog extends Model
{
    use HasFactory;

    protected $table = 'harga_sampah_log';

    public $timestamps = false;

    protected $fillable = [
        'jenis_sampah_id',
        'harga_lama',
        'harga_baru',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'harga_lama' => 'decimal:2',
        'harga_baru' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

