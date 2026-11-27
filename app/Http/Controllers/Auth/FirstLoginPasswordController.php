<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstLoginPasswordController extends Controller
{
    /**
     * Tampilkan formulir wajib ganti kata sandi bawaan.
     */
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        // Jika user tidak perlu ganti password, kembalikan ke dashboard
        if (! $user->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.first-login-change-password', [
            'user' => $user,
        ]);
    }

    /**
     * Proses pembaruan kata sandi bawaan.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini/bawaan wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini yang Anda masukkan tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru tidak boleh sama dengan kata sandi bawaan/saat ini.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'temp_password_encrypted' => null,
        ])->save();

        auditLog(
            action: 'update',
            module: 'Autentikasi',
            description: "Pengguna {$user->name} ({$user->email}) berhasil memperbarui kata sandi bawaan saat login pertama",
            user: $user
        );

        return redirect()->route('dashboard')->with('success', 'Kata sandi Anda berhasil diperbarui! Selamat datang di Bank Sampah.');
    }
}
