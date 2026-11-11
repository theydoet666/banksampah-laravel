<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'kode_unit',
        'nama',
        'alamat',
        'nama_ketua',
        'no_hp',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_id');
    }

    public function nasabah(): HasMany
    {
        return $this->hasMany(Nasabah::class, 'unit_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'unit_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}

