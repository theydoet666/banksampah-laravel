<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriSampah;

class KategoriSampahController extends Controller
{
    public function index()
    {
        $kategoriList = KategoriSampah::withCount('jenisSampah')->orderBy('nama')->get();
        return view('admin.kategori-sampah.index', compact('kategoriList'));
    }

    public function create()
    {
        return view('admin.kategori-sampah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_sampah,nama',
            'deskripsi' => 'nullable|string',
        ]);

        KategoriSampah::create($validated);

        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    public function edit(KategoriSampah $kategoriSampah)
    {
        return view('admin.kategori-sampah.edit', compact('kategoriSampah'));
    }

    public function update(Request $request, KategoriSampah $kategoriSampah)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_sampah,nama,' . $kategoriSampah->id,
            'deskripsi' => 'nullable|string',
        ]);

        $kategoriSampah->update($validated);

        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    public function destroy(KategoriSampah $kategoriSampah)
    {
        if ($kategoriSampah->jenisSampah()->count() > 0) {
            return back()->with('error', 'Gagal menghapus: Kategori ini masih memiliki jenis sampah terhubung.');
        }

        $kategoriSampah->delete();

        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil dihapus.');
    }
}

