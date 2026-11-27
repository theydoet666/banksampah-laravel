<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class NasabahActivationController extends Controller
{
    /**
     * Tampilkan formulir aktivasi akun mandiri nasabah (sekali pakai via No. Rekening + NIK + No. HP).
     */
    public function create(): View
    {
        return view('auth.aktivasi-nasabah');
    }

    /**
     * Proses aktivasi akun mandiri nasabah.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_rekening' => ['required', 'string', 'max:30'],
            'nik'         => ['required', 'string', 'digits:16'],
            'no_hp'       => ['required', 'string', 'max:20'],
            'password'    => ['required', 'confirmed', Password::defaults()],
        ], [
            'no_rekening.required' => 'Nomor rekening wajib diisi.',
            'nik.required'         => 'NIK KTP (16 digit) wajib diisi.',
            'nik.digits'           => 'NIK harus tepat terdiri dari 16 digit angka.',
            'no_hp.required'       => 'Nomor HP terdaftar wajib diisi.',
        ]);

        $noRekeningInput = Str::upper(trim($validated['no_rekening']));
        $throttleKey = 'nasabah_activate:' . $noRekeningInput . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return back()
                ->withInput($request->only('no_rekening'))
                ->withErrors(['no_rekening' => "Terlalu banyak percobaan aktivasi gagal. Silakan coba lagi dalam {$minutes} menit."]);
        }

        $genericError = 'Verifikasi identitas gagal. Pastikan No. Rekening, NIK, dan No. HP sesuai dengan data terdaftar, atau akun Anda sudah aktif (hubungi petugas jika lupa kata sandi).';

        $activatedUser = DB::transaction(function () use ($noRekeningInput, $validated) {
            $nasabah = Nasabah::with('user')
                ->where('no_rekening', $noRekeningInput)
                ->lockForUpdate()
                ->first();

            if (! $nasabah || $nasabah->status !== 'aktif' || ! $nasabah->user || ! $nasabah->user->is_active) {
                return null;
            }

            $user = $nasabah->user;

            // Wajib hanya sekali pakai saat akun belum pernah diaktivasi (must_change_password = true)
            if (! $user->must_change_password) {
                return null;
            }

            // NIK pada data nasabah tidak boleh kosong & harus cocok persis
            if (empty($nasabah->nik) || ! hash_equals((string) $nasabah->nik, (string) $validated['nik'])) {
                return null;
            }

            // No. HP dinormalisasi digit-only untuk pencocokan tambahan
            $storedPhone = preg_replace('/[^0-9]/', '', (string) $nasabah->no_hp);
            $inputPhone = preg_replace('/[^0-9]/', '', (string) $validated['no_hp']);
            if ($storedPhone === '' || ! hash_equals($storedPhone, $inputPhone)) {
                return null;
            }

            $user->forceFill([
                'password'                => Hash::make($validated['password']),
                'must_change_password'    => false,
                'temp_password_encrypted' => null,
            ])->save();

            auditLog(
                action: 'activate',
                module: 'Autentikasi',
                description: "Aktivasi akun mandiri nasabah berhasil via No. Rekening + NIK: {$nasabah->nama} ({$nasabah->no_rekening})",
                subject: $nasabah,
                user: $user
            );

            return $user;
        });

        if (! $activatedUser) {
            RateLimiter::hit($throttleKey, 600); // 10 menit

            auditLog(
                action: 'failed_activation',
                module: 'Autentikasi',
                description: "Percobaan aktivasi mandiri nasabah gagal untuk No. Rekening: " . Str::limit($noRekeningInput, 30)
            );

            return back()
                ->withInput($request->only('no_rekening'))
                ->withErrors(['no_rekening' => $genericError]);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($activatedUser);
        $request->session()->regenerate();

        return redirect()->route('nasabah.dashboard')
            ->with('success', 'Akun Anda berhasil diaktivasi! Selamat datang di Bank Sampah.');
    }
}
