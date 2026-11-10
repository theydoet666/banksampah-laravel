<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $nasabah = Nasabah::where('user_id', $user->id)->first();
        return view('nasabah.profil.edit', compact('user', 'nasabah'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $nasabah = Nasabah::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);

        $user->update(['name' => $validated['name']]);

        if ($nasabah) {
            $nasabah->update([
                'nama' => $validated['name'],
                'no_hp' => $validated['no_hp'],
                'alamat' => $validated['alamat'],
            ]);
        }

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}

