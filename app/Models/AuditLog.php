<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        });

        $query->when($filters['user_id'] ?? null, function ($q, $userId) {
            $q->where('user_id', $userId);
        });

        $query->when($filters['role'] ?? null, function ($q, $role) {
            $q->where('user_role', $role);
        });

        $query->when($filters['module'] ?? null, function ($q, $module) {
            $q->where('module', $module);
        });

        $query->when($filters['action'] ?? null, function ($q, $action) {
            $q->where('action', $action);
        });

        $query->when($filters['tanggal_dari'] ?? null, function ($q, $from) {
            $q->whereDate('created_at', '>=', $from);
        });

        $query->when($filters['tanggal_sampai'] ?? null, function ($q, $to) {
            $q->whereDate('created_at', '<=', $to);
        });

        return $query;
    }

    public function getActionBadgeAttribute(): string
    {
        $color = match ($this->action) {
            'login' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'logout' => 'bg-slate-100 text-slate-700 border-slate-200',
            'failed_login' => 'bg-rose-50 text-rose-700 border-rose-200',
            'create', 'saldo_awal' => 'bg-sky-50 text-sky-700 border-sky-200',
            'update', 'pelunasan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'delete', 'void', 'reject' => 'bg-rose-50 text-rose-700 border-rose-200',
            'approve' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'reset_password' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };

        $label = match ($this->action) {
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Gagal Login',
            'create' => 'Tambah Data',
            'update' => 'Ubah Data',
            'delete' => 'Hapus Data',
            'approve' => 'Setujui',
            'reject' => 'Tolak',
            'void' => 'Batalkan',
            'pelunasan' => 'Pelunasan',
            'saldo_awal' => 'Saldo Awal',
            'reset_password' => 'Reset Sandi',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };

        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {$color}\">{$label}</span>";
    }

    public function getRoleBadgeAttribute(): string
    {
        $color = match ($this->user_role) {
            'admin' => 'bg-purple-50 text-purple-700 border-purple-200',
            'pengelola' => 'bg-blue-50 text-blue-700 border-blue-200',
            'petugas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'nasabah' => 'bg-amber-50 text-amber-800 border-amber-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };

        $roleLabel = ucfirst($this->user_role ?? 'Sistem');

        return "<span class=\"inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {$color}\">{$roleLabel}</span>";
    }
}
