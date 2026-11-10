<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $nasabah = Nasabah::where('user_id', $user->id)->first();

        if (! $nasabah) {
            $transaksiList = collect();
            return view('nasabah.riwayat.index', compact('transaksiList', 'nasabah'));
        }

        $query = Transaksi::where('nasabah_id', $nasabah->id)->with('details.jenisSampah');

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $transaksiList = $query->orderBy('tanggal_transaksi', 'desc')->paginate(15)->withQueryString();

        return view('nasabah.riwayat.index', compact('transaksiList', 'nasabah'));
    }

    public function show(Transaksi $transaksi)
    {
        $user = auth()->user();
        $nasabah = Nasabah::where('user_id', $user->id)->first();

        if (! $nasabah || $transaksi->nasabah_id !== $nasabah->id) {
            abort(403, 'Akses ditolak.');
        }

        $transaksi->load(['details.jenisSampah', 'petugas']);

        return view('nasabah.riwayat.show', compact('transaksi'));
    }
}

