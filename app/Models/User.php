<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'unit_id',
        'is_active',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function nasabah(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Nasabah::class, 'user_id');
    }

    public function nasabahTerdaftar(): HasMany
    {
        return $this->hasMany(Nasabah::class, 'registered_by');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'petugas_id');
    }

    public function perubahanHarga(): HasMany
    {
        return $this->hasMany(HargaSampahLog::class, 'changed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPengelola(): bool
    {
        return $this->role === 'pengelola';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isNasabah(): bool
    {
        return $this->role === 'nasabah';
    }

    public function canManageFinanceAndWarehouse(): bool
    {
        return in_array($this->role, ['admin', 'pengelola']);
    }

    public function hasPin(): bool
    {
        return ! empty($this->pin);
    }

    public function verifyPin(?string $pin): bool
    {
        if (empty($pin) || empty($this->pin)) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($pin, $this->pin);
    }

    public function setPin(string $pin): void
    {
        $this->pin = \Illuminate\Support\Facades\Hash::make($pin);
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Set role user secara aman dengan validasi whitelist.
     * Gunakan method ini alih-alih mass assignment langsung.
     */
    public function assignRole(string $role): void
    {
        $allowed = ['admin', 'pengelola', 'petugas', 'nasabah'];
        if (! in_array($role, $allowed)) {
            throw new \InvalidArgumentException("Role tidak valid: {$role}");
        }
        $this->forceFill(['role' => $role])->save();
    }

    /**
     * Buat user baru dengan role secara aman (bypass fillable untuk role saja).
     */
    public static function createWithRole(array $attributes, string $role): static
    {
        $allowed = ['admin', 'pengelola', 'petugas', 'nasabah'];
        if (! in_array($role, $allowed)) {
            throw new \InvalidArgumentException("Role tidak valid: {$role}");
        }
        $user = static::create($attributes);
        $user->forceFill(['role' => $role])->save();
        return $user->fresh();
    }
}
