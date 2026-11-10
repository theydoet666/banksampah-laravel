<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\JenisSampah;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalNasabah = Nasabah::aktif()->count();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Total sampah masuk (kg) bulan ini
        $totalSampahBulanIni = DetailTransaksi::whereHas('transaksi', function ($query) use ($startOfMonth, $endOfMonth) {
            $query->where('jenis', 'setor')
                ->where('status', 'aktif')
                ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth]);
        })->sum('berat');

        // Total nilai setoran bulan ini
        $totalSetoranBulanIni = Transaksi::setor()
            ->aktif()
            ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
            ->sum('total_nilai');

        // Total nilai penarikan bulan ini
        $totalPenarikanBulanIni = Transaksi::tarik()
            ->aktif()
            ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
            ->sum('total_nilai');

        $totalJenisSampah = JenisSampah::active()->count();

        $transaksiTerbaru = Transaksi::with(['nasabah', 'petugas'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Data transaksi 7 hari terakhir untuk Chart.js
        $chartLabels = [];
        $chartSetor = [];
        $chartTarik = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayName = Carbon::now()->subDays($i)->locale('id')->isoFormat('ddd, D MMM');
            $chartLabels[] = $dayName;

            $setorNominal = Transaksi::setor()
                ->aktif()
                ->whereDate('tanggal_transaksi', $date)
                ->sum('total_nilai');

            $tarikNominal = Transaksi::tarik()
                ->aktif()
                ->whereDate('tanggal_transaksi', $date)
                ->sum('total_nilai');

            $chartSetor[] = (float) $setorNominal;
            $chartTarik[] = (float) $tarikNominal;
        }

        $chartData = [
            'labels' => $chartLabels,
            'setor' => $chartSetor,
            'tarik' => $chartTarik,
        ];

        return view('admin.dashboard', compact(
            'totalNasabah',
            'totalSampahBulanIni',
            'totalSetoranBulanIni',
            'totalPenarikanBulanIni',
            'totalJenisSampah',
            'transaksiTerbaru',
            'chartData'
        ));
    }
}

