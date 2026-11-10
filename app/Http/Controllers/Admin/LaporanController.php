<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
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

        $transaksiList = (clone $query)->orderBy('tanggal_transaksi', 'desc')->paginate(25)->withQueryString();

        $totalSetor = (clone $query)->where('jenis', 'setor')->where('status', 'aktif')->sum('total_nilai');
        $totalTarik = (clone $query)->where('jenis', 'tarik')->where('status', 'aktif')->sum('total_nilai');
        $totalBerat = (clone $query)->where('jenis', 'setor')->where('status', 'aktif')->sum('total_berat');

        $nasabahList = Nasabah::orderBy('nama')->get();

        return view('admin.laporan.index', compact('transaksiList', 'totalSetor', 'totalTarik', 'totalBerat', 'nasabahList'));
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

