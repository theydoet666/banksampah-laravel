<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $nasabah = Nasabah::where('user_id', $user->id)->first();

        $saldo = $nasabah ? $nasabah->saldo : 0;
        $noRekening = $nasabah ? $nasabah->no_rekening : '-';

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $setorBulanIni = 0;
        $tarikBulanIni = 0;
        $transaksiTerbaru = collect();

        if ($nasabah) {
            $setorBulanIni = Transaksi::setor()
                ->aktif()
                ->where('nasabah_id', $nasabah->id)
                ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
                ->sum('total_nilai');

            $tarikBulanIni = Transaksi::tarik()
                ->aktif()
                ->where('nasabah_id', $nasabah->id)
                ->whereBetween('tanggal_transaksi', [$startOfMonth, $endOfMonth])
                ->sum('total_nilai');

            $transaksiTerbaru = Transaksi::where('nasabah_id', $nasabah->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
        }

        return view('nasabah.dashboard', compact(
            'nasabah',
            'saldo',
            'noRekening',
            'setorBulanIni',
            'tarikBulanIni',
            'transaksiTerbaru'
        ));
    }
}

