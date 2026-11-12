<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class NasabahRegisterController extends Controller
{
    public function create()
    {
        $units = Unit::aktif()->orderBy('nama')->get();
        return view('auth.register-nasabah', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|digits:16|unique:nasabah,nik',
            'no_hp' => 'required|string|max:20|unique:nasabah,no_hp',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'unit_id' => 'required|exists:units,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nik.unique' => 'NIK ini sudah terdaftar sebagai nasabah.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar sebagai nasabah.',
            'unit_id.required' => 'Pilih dusun/RW unit bank sampah tempat tinggal Anda.',
        ]);

        $noRekening = Nasabah::generateNoRekening();
        $generatedEmail = strtolower(str_replace('-', '', $noRekening)) . '@nasabah.banksampah.test';

        $user = DB::transaction(function () use ($validated, $noRekening, $generatedEmail) {
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $generatedEmail,
                'password' => Hash::make($validated['password']),
                'role' => 'nasabah',
                'is_active' => true,
                'unit_id' => $validated['unit_id'],
            ]);

            Nasabah::create([
                'user_id' => $user->id,
                'unit_id' => $validated['unit_id'],
                'no_rekening' => $noRekening,
                'nama' => $validated['nama'],
                'nik' => $validated['nik'] ?? null,
                'no_hp' => $validated['no_hp'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'],
                'status' => 'aktif',
                'saldo' => 0,
                'registered_by' => $user->id,
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('nasabah.dashboard')->with('success', "Selamat datang di Bank Sampah! Buku tabungan digital Anda aktif dengan No. Rekening: {$noRekening}.");
    }
}
