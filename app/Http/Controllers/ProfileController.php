<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // 1. Larang penghapusan akun Administrator terakhir
        if ($user->isAdmin()) {
            $activeAdminCount = \App\Models\User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                return back()->withErrors(['userDeletion' => 'Akun Administrator utama/terakhir tidak dapat dihapus demi kelangsungan operasional sistem.'], 'userDeletion');
            }
        }

        // 2. Larang nasabah menghapus akun jika masih memiliki saldo aktif atau saldo tertahan
        if ($user->isNasabah()) {
            $nasabah = \App\Models\Nasabah::where('user_id', $user->id)->first();
            if ($nasabah) {
                if ($nasabah->saldo > 0 || $nasabah->saldo_tertahan > 0) {
                    return back()->withErrors(['userDeletion' => 'Akun tidak dapat dihapus karena masih memiliki saldo aktif (' . formatRupiah($nasabah->saldo) . ') atau transaksi penarikan yang sedang berjalan.'], 'userDeletion');
                }
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's transaction authorization PIN.
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'current_password' => ['required', 'current_password'],
            'pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ];

        $validated = $request->validate($rules, [
            'pin.required' => 'PIN otorisasi 6-digit wajib diisi.',
            'pin.digits' => 'PIN otorisasi harus tepat terdiri dari 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok dengan PIN baru.',
            'current_password.required' => 'Password saat ini diperlukan untuk verifikasi keamanan pembuatan/perubahan PIN.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ]);

        $user->setPin($validated['pin']);

        return back()->with('success', 'PIN Otorisasi Transaksi 6-digit berhasil disimpan!');
    }
}
