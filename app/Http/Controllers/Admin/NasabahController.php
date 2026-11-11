<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

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

        $validated['no_rekening'] = Nasabah::generateNoRekening();
        $validated['registered_by'] = auth()->id();
        $validated['saldo'] = 0;

        Nasabah::create($validated);

        return redirect()->route('admin.nasabah.index')->with('success', 'Nasabah baru berhasil didaftarkan.');
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
}

