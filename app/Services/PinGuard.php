<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class PinGuard
{
    /**
     * Memverifikasi PIN otorisasi user dengan rate limiting per akun (5 gagal / 15 menit)
     * dan pencatatan audit log otomatis pada setiap kegagalan.
     *
     * @param User $user
     * @param string|null $pin
     * @return string|null Mengembalikan null jika valid, atau string pesan error jika tidak valid/terkunci
     */
    public static function check(User $user, ?string $pin): ?string
    {
        if (! $user->hasPin()) {
            return 'Akun Anda belum memiliki PIN Otorisasi Transaksi. Silakan atur PIN 6-digit terlebih dahulu pada menu Profil.';
        }

        $rateLimitKey = 'pin_check:' . $user->id;
        $maxAttempts = 5;
        $decaySeconds = 900; // 15 menit

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $secondsRemaining = RateLimiter::availableIn($rateLimitKey);
            $minutesRemaining = ceil($secondsRemaining / 60);

            auditLog(
                action: 'pin_locked',
                module: 'Keamanan PIN',
                description: "Percobaan verifikasi PIN diblokir karena terlalu banyak kesalahan (terkunci selama {$minutesRemaining} menit lagi).",
                user: $user
            );

            return "Akses PIN terkunci karena terlalu banyak percobaan salah (5 kali). Silakan coba lagi dalam {$minutesRemaining} menit.";
        }

        if (empty($pin) || ! $user->verifyPin($pin)) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);
            $attemptsLeft = RateLimiter::remaining($rateLimitKey, $maxAttempts);

            auditLog(
                action: 'failed_pin',
                module: 'Keamanan PIN',
                description: "Percobaan memasukkan PIN otorisasi salah. Sisa kesempatan: {$attemptsLeft} kali.",
                user: $user
            );

            if ($attemptsLeft <= 0) {
                return 'PIN Otorisasi salah! Akses PIN akun Anda kini terkunci selama 15 menit demi keamanan.';
            }

            return "PIN Otorisasi salah! Sisa percobaan: {$attemptsLeft} kali sebelum akun terkunci selama 15 menit.";
        }

        // Jika berhasil, bersihkan hit percobaan gagal
        RateLimiter::clear($rateLimitKey);

        return null;
    }
}
