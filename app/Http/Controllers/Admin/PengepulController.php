<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengepul;
use Illuminate\Http\Request;

class PengepulController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengepul::withCount('penjualan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengepulList = $query->latest()->paginate(10)->withQueryString();

        return view('admin.pengepul.index', compact('pengepulList'));
    }

    public function create()
    {
        return view('admin.pengepul.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $pengepul = Pengepul::create($validated);

        return redirect()->route('admin.pengepul.index')
            ->with('success', "Mitra pengepul '{$pengepul->nama_perusahaan}' berhasil ditambahkan.");
    }

    public function edit(Pengepul $pengepul)
    {
        return view('admin.pengepul.edit', compact('pengepul'));
    }

    public function update(Request $request, Pengepul $pengepul)
    {
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $pengepul->update($validated);

        return redirect()->route('admin.pengepul.index')
            ->with('success', "Data mitra '{$pengepul->nama_perusahaan}' berhasil diperbarui.");
    }

    public function toggle(Pengepul $pengepul)
    {
        $newStatus = $pengepul->status === 'aktif' ? 'nonaktif' : 'aktif';
        $pengepul->update(['status' => $newStatus]);

        return back()->with('success', "Status mitra '{$pengepul->nama_perusahaan}' diubah menjadi {$newStatus}.");
    }
}
