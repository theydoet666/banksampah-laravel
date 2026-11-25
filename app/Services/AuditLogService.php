<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Record an audit log entry.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null
    ): ?AuditLog {
        try {
            $currentUser = $user ?? auth()->user();

            return AuditLog::create([
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? 'Sistem / Tamu',
                'user_role' => $currentUser?->role ?? 'guest',
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log failure should never interrupt user transaction, but log error with context
            Log::channel('single')->error('Gagal mencatat audit log: ' . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'user_id' => $currentUser?->id,
                'exception' => $e,
            ]);
            report($e);
            return null;
        }
    }
}
