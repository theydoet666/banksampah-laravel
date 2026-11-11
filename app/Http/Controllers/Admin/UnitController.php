<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::withCount(['nasabah', 'transaksi', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_unit', 'like', "%{$search}%")
                  ->orWhere('nama_ketua', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->latest()->paginate(10)->withQueryString();

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        // Generate automatic kode unit if possible
        $lastUnit = Unit::orderBy('id', 'desc')->first();
        $nextNum = $lastUnit ? ((int) preg_replace('/[^0-9]/', '', $lastUnit->kode_unit)) + 1 : 1;
        $suggestedKode = sprintf('U-%02d', $nextNum);

        return view('admin.units.create', compact('suggestedKode'));
    }

    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', "Unit bank sampah '{$unit->nama}' berhasil ditambahkan.");
    }

    public function show(Unit $unit)
    {
        $unit->loadCount(['nasabah', 'transaksi', 'users']);
        $recentTransactions = $unit->transaksi()->with('nasabah', 'petugas')->latest()->take(10)->get();
        $petugasList = $unit->users()->where('role', 'petugas')->get();

        return view('admin.units.show', compact('unit', 'recentTransactions', 'petugasList'));
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', "Data unit '{$unit->nama}' berhasil diperbarui.");
    }

    public function toggle(Unit $unit)
    {
        $newStatus = $unit->status === 'aktif' ? 'nonaktif' : 'aktif';
        $unit->update(['status' => $newStatus]);

        return back()->with('success', "Status unit '{$unit->nama}' berhasil diubah menjadi {$newStatus}.");
    }
}
