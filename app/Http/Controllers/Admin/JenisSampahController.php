<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;
use App\Models\HargaSampahLog;
use Illuminate\Support\Facades\DB;

class JenisSampahController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisSampah::with('kategori');

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $jenisSampahList = $query->orderBy('nama')->paginate(15)->withQueryString();
        $kategoriList = KategoriSampah::orderBy('nama')->get();

        return view('admin.jenis-sampah.index', compact('jenisSampahList', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = KategoriSampah::orderBy('nama')->get();
        return view('admin.jenis-sampah.create', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_sampah,id',
            'nama' => 'required|string|max:100',
            'satuan' => 'required|in:kg,gram',
            'harga_per_satuan' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($validated) {
            $jenisSampah = JenisSampah::create($validated);

            HargaSampahLog::create([
                'jenis_sampah_id' => $jenisSampah->id,
                'harga_lama' => 0,
                'harga_baru' => $validated['harga_per_satuan'],
                'changed_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.jenis-sampah.index')->with('success', 'Jenis sampah berhasil ditambahkan.');
    }

    public function edit(JenisSampah $jenisSampah)
    {
        $kategoriList = KategoriSampah::orderBy('nama')->get();
        return view('admin.jenis-sampah.edit', compact('jenisSampah', 'kategoriList'));
    }

    public function update(Request $request, JenisSampah $jenisSampah)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_sampah,id',
            'nama' => 'required|string|max:100',
            'satuan' => 'required|in:kg,gram',
            'harga_per_satuan' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $oldPrice = (float) $jenisSampah->harga_per_satuan;
        $newPrice = (float) $validated['harga_per_satuan'];

        DB::transaction(function () use ($jenisSampah, $validated, $oldPrice, $newPrice) {
            if ($oldPrice !== $newPrice) {
                HargaSampahLog::create([
                    'jenis_sampah_id' => $jenisSampah->id,
                    'harga_lama' => $oldPrice,
                    'harga_baru' => $newPrice,
                    'changed_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            $jenisSampah->update($validated);
        });

        return redirect()->route('admin.jenis-sampah.index')->with('success', 'Jenis sampah berhasil diperbarui.');
    }

    public function toggle(JenisSampah $jenisSampah)
    {
        $jenisSampah->update(['is_active' => ! $jenisSampah->is_active]);
        $status = $jenisSampah->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Jenis sampah {$jenisSampah->nama} berhasil {$status}.");
    }

    public function hargaLog(JenisSampah $jenisSampah)
    {
        $logs = $jenisSampah->hargaLogs()->with('user')->orderBy('id', 'desc')->get();
        return view('admin.jenis-sampah.harga-log', compact('jenisSampah', 'logs'));
    }
}

