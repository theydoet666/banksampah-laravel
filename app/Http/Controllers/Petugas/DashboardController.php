<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $transaksiHariIni = Transaksi::where('petugas_id', $userId)
            ->whereDate('tanggal_transaksi', $today)
            ->count();

        $transaksiBulanIni = Transaksi::where('petugas_id', $userId)
            ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
            ->count();

        $nilaiTransaksiBulanIni = Transaksi::where('petugas_id', $userId)
            ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
            ->where('status', 'aktif')
            ->sum('total_nilai');

        $transaksiTerbaru = Transaksi::with(['nasabah'])
            ->where('petugas_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('petugas.dashboard', compact(
            'transaksiHariIni',
            'transaksiBulanIni',
            'nilaiTransaksiBulanIni',
            'transaksiTerbaru'
        ));
    }
}

