<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengepul extends Model
{
    use HasFactory;

    protected $table = 'pengepul';

    protected $fillable = [
        'nama_perusahaan',
        'penanggung_jawab',
        'no_hp',
        'alamat',
        'status',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(PenjualanSampah::class, 'pengepul_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
