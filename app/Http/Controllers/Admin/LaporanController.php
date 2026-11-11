<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\Unit;
use App\Models\PenjualanSampah;
use App\Models\DetailTransaksi;
use App\Models\DetailPenjualanSampah;
use App\Models\KategoriSampah;
use App\Exports\TransaksiExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nasabah_id')) {
            $query->where('nasabah_id', $request->nasabah_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $transaksiList = (clone $query)->orderBy('tanggal_transaksi', 'desc')->paginate(25)->withQueryString();

        $totalSetor = (clone $query)->where('jenis', 'setor')->where('status', 'aktif')->sum('total_nilai');
        $totalTarik = (clone $query)->where('jenis', 'tarik')->where('status', 'aktif')->sum('total_nilai');
        $totalBerat = (clone $query)->where('jenis', 'setor')->where('status', 'aktif')->sum('total_berat');

        $nasabahList = Nasabah::orderBy('nama')->get();
        $units = Unit::aktif()->orderBy('nama')->get();

        return view('admin.laporan.index', compact('transaksiList', 'totalSetor', 'totalTarik', 'totalBerat', 'nasabahList', 'units'));
    }

    public function labaRugi(Request $request)
    {
        $tanggalDari = $request->input('tanggal_dari', now()->startOfMonth()->toDateString());
        $tanggalSampai = $request->input('tanggal_sampai', now()->endOfMonth()->toDateString());

        // Pendapatan dari Penjualan ke Pengepul
        $penjualanQuery = PenjualanSampah::whereBetween('tanggal_penjualan', [$tanggalDari, $tanggalSampai]);
        $totalPendapatanJual = (clone $penjualanQuery)->sum('total_pendapatan');
        $totalBeratJual = (clone $penjualanQuery)->sum('total_berat');
        $penjualanList = (clone $penjualanQuery)->with('pengepul', 'details.jenisSampah')->latest('tanggal_penjualan')->get();

        // Beban dari Pembelian Sampah Setoran Warga (Status Aktif)
        $setoranQuery = Transaksi::where('jenis', 'setor')->where('status', 'aktif')
            ->whereBetween('tanggal_transaksi', [$tanggalDari, $tanggalSampai]);
        $totalBebanBeli = (clone $setoranQuery)->sum('total_nilai');
        $totalBeratBeli = (clone $setoranQuery)->sum('total_berat');

        // Laba Kotor Operasional Desa
        $labaKotor = $totalPendapatanJual - $totalBebanBeli;
        $marginPersen = $totalPendapatanJual > 0 ? round(($labaKotor / $totalPendapatanJual) * 100, 1) : 0;

        return view('admin.laporan.laba-rugi', compact(
            'tanggalDari',
            'tanggalSampai',
            'totalPendapatanJual',
            'totalBeratJual',
            'totalBebanBeli',
            'totalBeratBeli',
            'labaKotor',
            'marginPersen',
            'penjualanList'
        ));
    }

    public function exportLabaRugiPdf(Request $request)
    {
        $tanggalDari = $request->input('tanggal_dari', now()->startOfMonth()->toDateString());
        $tanggalSampai = $request->input('tanggal_sampai', now()->endOfMonth()->toDateString());

        $penjualanQuery = PenjualanSampah::whereBetween('tanggal_penjualan', [$tanggalDari, $tanggalSampai]);
        $totalPendapatanJual = (clone $penjualanQuery)->sum('total_pendapatan');
        $totalBeratJual = (clone $penjualanQuery)->sum('total_berat');
        $penjualanList = (clone $penjualanQuery)->with('pengepul', 'details.jenisSampah')->latest('tanggal_penjualan')->get();

        $setoranQuery = Transaksi::where('jenis', 'setor')->where('status', 'aktif')
            ->whereBetween('tanggal_transaksi', [$tanggalDari, $tanggalSampai]);
        $totalBebanBeli = (clone $setoranQuery)->sum('total_nilai');
        $totalBeratBeli = (clone $setoranQuery)->sum('total_berat');

        $labaKotor = $totalPendapatanJual - $totalBebanBeli;
        $marginPersen = $totalPendapatanJual > 0 ? round(($labaKotor / $totalPendapatanJual) * 100, 1) : 0;

        $pdf = Pdf::loadView('admin.laporan.laba-rugi-pdf', compact(
            'tanggalDari',
            'tanggalSampai',
            'totalPendapatanJual',
            'totalBeratJual',
            'totalBebanBeli',
            'totalBeratBeli',
            'labaKotor',
            'marginPersen',
            'penjualanList'
        ));

        return $pdf->download("laporan-laba-rugi-desa-{$tanggalDari}-sd-{$tanggalSampai}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getFilteredData($request);
        return Excel::download(new TransaksiExport($data), 'laporan-transaksi-bank-sampah.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $transaksi = $this->getFilteredData($request);
        $totalSetor = $transaksi->where('jenis', 'setor')->where('status', 'aktif')->sum('total_nilai');
        $totalTarik = $transaksi->where('jenis', 'tarik')->where('status', 'aktif')->sum('total_nilai');
        $totalBerat = $transaksi->where('jenis', 'setor')->where('status', 'aktif')->sum('total_berat');

        $pdf = Pdf::loadView('admin.laporan.pdf', compact('transaksi', 'totalSetor', 'totalTarik', 'totalBerat'));
        return $pdf->download('laporan-transaksi-bank-sampah.pdf');
    }

    protected function getFilteredData(Request $request)
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nasabah_id')) {
            $query->where('nasabah_id', $request->nasabah_id);
        }

        return $query->orderBy('tanggal_transaksi', 'desc')->get();
    }
}

