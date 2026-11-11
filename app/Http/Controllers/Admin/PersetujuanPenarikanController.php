<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Exception;

class PersetujuanPenarikanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['nasabah.unit', 'petugas'])
            ->where('jenis', 'tarik')
            ->orderBy('id', 'desc');

        // Filter status (default: menunggu_persetujuan)
        $status = $request->get('status', 'menunggu_persetujuan');
        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $transaksis = $query->paginate(15)->withQueryString();
        $totalMenunggu = Transaksi::where('jenis', 'tarik')->where('status', 'menunggu_persetujuan')->count();
        $nominalMenunggu = Transaksi::where('jenis', 'tarik')->where('status', 'menunggu_persetujuan')->sum('total_nilai');

        $units = \App\Models\Unit::orderBy('nama')->get();

        return view('admin.transaksi.persetujuan.index', compact('transaksis', 'totalMenunggu', 'nominalMenunggu', 'units', 'status'));
    }

    public function setujui(Request $request, Transaksi $transaksi, TransaksiService $transaksiService)
    {
        try {
            $transaksiService->setujuiPenarikan($transaksi, auth()->user());
            return back()->with('success', "Penarikan saldo sebesar Rp " . number_format($transaksi->total_nilai, 0, ',', '.') . " untuk nasabah {$transaksi->nasabah->nama} berhasil disetujui & dicairkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolak(Request $request, Transaksi $transaksi, TransaksiService $transaksiService)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        try {
            $transaksiService->tolakPenarikan($transaksi, auth()->user(), $request->alasan_penolakan);
            return back()->with('success', "Pengajuan penarikan dana untuk nasabah {$transaksi->nasabah->nama} telah ditolak.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
