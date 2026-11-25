<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Models\Nasabah;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->input('email', ''));
        $password = $this->input('password');

        // Cari user melalui Email saja (hapus orWhere name demi keamanan)
        $user = User::where('email', $loginInput)->first();

        // Shorthand username jika admin mengetik 'admin', 'pengelola', 'petugas' (dibatasi hanya local/testing)
        if (! $user && app()->environment(['local', 'testing'])) {
            $normalized = strtolower($loginInput);
            if (in_array($normalized, ['admin', 'administrator', 'pengelola', 'petugas', 'nasabah'])) {
                $roleSearch = $normalized === 'administrator' ? 'admin' : $normalized;
                $user = User::where('email', "{$roleSearch}@banksampah.test")
                    ->orWhere('role', $roleSearch)
                    ->first();
            }
        }

        if (! $user) {
            $nasabah = Nasabah::where('no_rekening', $loginInput)->first();
            if ($nasabah && $nasabah->user_id) {
                $user = $nasabah->user;
            }
        }

        if (! $user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $loginInput);
            $nasabah = Nasabah::where('no_hp', $loginInput)
                ->when($cleanPhone, fn($q) => $q->orWhere('no_hp', $cleanPhone))
                ->first();
            if ($nasabah && $nasabah->user_id) {
                $user = $nasabah->user;
            }
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->hitRateLimiters();

            // Audit log login gagal (potong identitas maks 100 karakter, jangan simpan password)
            $maskedIdentity = Str::limit($loginInput, 100, '...');
            auditLog(
                action: 'failed_login',
                module: 'Autentikasi',
                description: "Percobaan login gagal untuk identitas: '{$maskedIdentity}'",
                user: $user ?: null
            );

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            $this->hitRateLimiters();

            $maskedIdentity = Str::limit($loginInput, 100, '...');
            auditLog(
                action: 'failed_login',
                module: 'Autentikasi',
                description: "Percobaan login akun nonaktif untuk identitas: '{$maskedIdentity}'",
                user: $user
            );

            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi pengelola bank sampah.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // 1. Limiter per identitas email/IP (5 percobaan / 1 menit)
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 2. Limiter kedua berbasis IP secara global (20 percobaan / 5 menit)
        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), 20)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->ipThrottleKey());

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login dari alamat IP Anda. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit.",
            ]);
        }
    }

    /**
     * Hit both rate limiters.
     */
    protected function hitRateLimiters(): void
    {
        RateLimiter::hit($this->throttleKey());
        RateLimiter::hit($this->ipThrottleKey(), 300); // 5 menit
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Get IP-based rate limiting key.
     */
    public function ipThrottleKey(): string
    {
        return 'login_ip:' . $this->ip();
    }
}
