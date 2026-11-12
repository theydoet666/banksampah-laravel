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
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'harga_per_satuan' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $hargaBeli = (float) ($validated['harga_beli'] ?? $validated['harga_per_satuan'] ?? 0);
        $hargaJual = (float) ($validated['harga_jual'] ?? round($hargaBeli * 1.30, -1));

        $validated['harga_beli'] = $hargaBeli;
        $validated['harga_jual'] = $hargaJual;
        $validated['harga_per_satuan'] = $hargaBeli;
        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($validated, $hargaBeli, $hargaJual) {
            $jenisSampah = JenisSampah::create($validated);

            HargaSampahLog::create([
                'jenis_sampah_id' => $jenisSampah->id,
                'tipe_perubahan' => 'keduanya',
                'harga_lama' => 0,
                'harga_baru' => $hargaBeli,
                'harga_beli_lama' => 0,
                'harga_beli_baru' => $hargaBeli,
                'harga_jual_lama' => 0,
                'harga_jual_baru' => $hargaJual,
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
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'harga_per_satuan' => 'nullable|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldBeli = (float) $jenisSampah->harga_beli;
        $oldJual = (float) $jenisSampah->harga_jual;

        $newBeli = (float) ($validated['harga_beli'] ?? $validated['harga_per_satuan'] ?? $oldBeli);
        $newJual = (float) ($validated['harga_jual'] ?? $oldJual);

        $validated['harga_beli'] = $newBeli;
        $validated['harga_jual'] = $newJual;
        $validated['harga_per_satuan'] = $newBeli;
        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($jenisSampah, $validated, $oldBeli, $newBeli, $oldJual, $newJual) {
            $changedBeli = ($oldBeli !== $newBeli);
            $changedJual = ($oldJual !== $newJual);

            if ($changedBeli || $changedJual) {
                $tipe = ($changedBeli && $changedJual) ? 'keduanya' : ($changedBeli ? 'beli' : 'jual');

                HargaSampahLog::create([
                    'jenis_sampah_id' => $jenisSampah->id,
                    'tipe_perubahan' => $tipe,
                    'harga_lama' => $oldBeli,
                    'harga_baru' => $newBeli,
                    'harga_beli_lama' => $oldBeli,
                    'harga_beli_baru' => $newBeli,
                    'harga_jual_lama' => $oldJual,
                    'harga_jual_baru' => $newJual,
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
