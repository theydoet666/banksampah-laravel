<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class NasabahController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Nasabah::with(['petugasPendaftar', 'user', 'unit']);

        // Scope Petugas ke Unit-nya sendiri
        if ($user && $user->isPetugas() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nasabahList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $units = Unit::aktif()->orderBy('nama')->get();

        return view('admin.nasabah.index', compact('nasabahList', 'units'));
    }

    public function create()
    {
        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.nasabah.create', compact('units'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|digits:16|unique:nasabah,nik',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,nonaktif',
        ];

        if ($user && $user->isPetugas() && $user->unit_id) {
            $rules['unit_id'] = 'nullable';
        } else {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $validated = $request->validate($rules);

        if ($user && $user->isPetugas() && $user->unit_id) {
            $validated['unit_id'] = $user->unit_id;
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('nasabah', 'public');
        }

        $noRekening = Nasabah::generateNoRekening();
        $validated['no_rekening'] = $noRekening;
        $validated['registered_by'] = auth()->id();
        $validated['saldo'] = 0;

        // Default password: 6 digit terakhir No HP (atau '123456' jika no HP pendek)
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['no_hp']);
        $defaultPassword = strlen($cleanPhone) >= 6 ? substr($cleanPhone, -6) : '123456';
        $generatedEmail = strtolower(str_replace('-', '', $noRekening)) . '@nasabah.banksampah.test';

        DB::transaction(function () use ($validated, $defaultPassword, $generatedEmail) {
            $userNasabah = User::create([
                'name' => $validated['nama'],
                'email' => $generatedEmail,
                'password' => Hash::make($defaultPassword),
                'role' => 'nasabah',
                'is_active' => true,
                'unit_id' => $validated['unit_id'] ?? null,
            ]);

            $validated['user_id'] = $userNasabah->id;
            Nasabah::create($validated);
        });

        $msg = "Nasabah baru berhasil didaftarkan! No. Rekening: {$noRekening}. Akun login mandiri dibuat otomatis (Login: No. Rekening / No. HP, Password: {$defaultPassword}).";

        return redirect()->route('admin.nasabah.index')->with('success', $msg);
    }

    public function show(Nasabah $nasabah)
    {
        $nasabah->load(['petugasPendaftar', 'unit', 'transaksi' => function ($q) {
            $q->orderBy('id', 'desc')->limit(10);
        }]);

        return view('admin.nasabah.show', compact('nasabah'));
    }

    public function edit(Nasabah $nasabah)
    {
        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.nasabah.edit', compact('nasabah', 'units'));
    }

    public function update(Request $request, Nasabah $nasabah)
    {
        $user = auth()->user();

        $rules = [
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|digits:16|unique:nasabah,nik,' . $nasabah->id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,nonaktif',
        ];

        if ($user && $user->isAdmin()) {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('foto')) {
            if ($nasabah->foto && Storage::disk('public')->exists($nasabah->foto)) {
                Storage::disk('public')->delete($nasabah->foto);
            }
            $validated['foto'] = $request->file('foto')->store('nasabah', 'public');
        }

        $nasabah->update($validated);

        return redirect()->route('admin.nasabah.show', $nasabah)->with('success', 'Data nasabah berhasil diperbarui.');
    }

    public function toggleStatus(Nasabah $nasabah)
    {
        $newStatus = $nasabah->status === 'aktif' ? 'nonaktif' : 'aktif';
        $nasabah->update(['status' => $newStatus]);

        return back()->with('success', "Status nasabah {$nasabah->nama} diubah menjadi {$newStatus}.");
    }

    public function resetPassword(Nasabah $nasabah)
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $nasabah->no_hp);
        $defaultPassword = strlen($cleanPhone) >= 6 ? substr($cleanPhone, -6) : '123456';

        if ($nasabah->user) {
            $nasabah->user->update([
                'password' => Hash::make($defaultPassword),
                'is_active' => true,
            ]);
        } else {
            // Jika belum punya akun user, buatkan seketika
            $generatedEmail = strtolower(str_replace('-', '', $nasabah->no_rekening)) . '@nasabah.banksampah.test';
            $user = User::create([
                'name' => $nasabah->nama,
                'email' => $generatedEmail,
                'password' => Hash::make($defaultPassword),
                'role' => 'nasabah',
                'is_active' => true,
                'unit_id' => $nasabah->unit_id,
            ]);
            $nasabah->update(['user_id' => $user->id]);
        }

        return back()->with('success', "Password akun nasabah {$nasabah->nama} berhasil direset menjadi: {$defaultPassword} (6 digit No. HP).");
    }
}

