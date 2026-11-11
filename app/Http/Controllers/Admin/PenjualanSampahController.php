<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenjualanSampah;
use App\Models\Pengepul;
use App\Models\JenisSampah;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Exception;

class PenjualanSampahController extends Controller
{
    protected PenjualanService $penjualanService;

    public function __construct(PenjualanService $penjualanService)
    {
        $this->penjualanService = $penjualanService;
    }

    public function index(Request $request)
    {
        $query = PenjualanSampah::with(['pengepul', 'pengelola', 'details.jenisSampah']);

        if ($request->filled('pengepul_id')) {
            $query->where('pengepul_id', $request->pengepul_id);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        $penjualanList = $query->latest('tanggal_penjualan')->paginate(15)->withQueryString();
        $pengepulList = Pengepul::aktif()->orderBy('nama_perusahaan')->get();

        return view('admin.penjualan.index', compact('penjualanList', 'pengepulList'));
    }

    public function stok()
    {
        $stokGudang = $this->penjualanService->getStokSampahGudang();
        $totalBeratSisa = $stokGudang->sum('sisa_stok');
        $totalEstimasiNilai = $stokGudang->sum('estimasi_nilai');
        $totalMasuk = $stokGudang->sum('total_masuk');
        $totalTerjual = $stokGudang->sum('total_terjual');

        return view('admin.penjualan.stok', compact('stokGudang', 'totalBeratSisa', 'totalEstimasiNilai', 'totalMasuk', 'totalTerjual'));
    }

    public function create()
    {
        $pengepulList = Pengepul::aktif()->orderBy('nama_perusahaan')->get();
        $stokGudang = $this->penjualanService->getStokSampahGudang();
        // Hanya sampah yang punya sisa stok > 0
        $availableJenis = $stokGudang->where('sisa_stok', '>', 0)->values();

        return view('admin.penjualan.create', compact('pengepulList', 'availableJenis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengepul_id' => 'required|exists:pengepul,id',
            'tanggal_penjualan' => 'required|date|before_or_equal:today',
            'status_pembayaran' => 'required|in:lunas,piutang',
            'catatan' => 'nullable|string|max:500',
            'details' => 'required|array|min:1',
            'details.*.jenis_sampah_id' => 'required|exists:jenis_sampah,id',
            'details.*.berat' => 'required|numeric|min:0.1',
            'details.*.harga_jual_satuan' => 'required|numeric|min:100',
        ]);

        try {
            $penjualan = $this->penjualanService->simpanPenjualan($validated);

            return redirect()->route('admin.penjualan.show', $penjualan)
                ->with('success', "Transaksi penjualan sampah [{$penjualan->kode_penjualan}] berhasil dicatat.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(PenjualanSampah $penjualan)
    {
        $penjualan->load(['pengepul', 'pengelola', 'details.jenisSampah.kategori']);
        return view('admin.penjualan.show', compact('penjualan'));
    }

    public function suratJalan(PenjualanSampah $penjualan)
    {
        $penjualan->load(['pengepul', 'pengelola', 'details.jenisSampah.kategori']);
        return view('admin.penjualan.surat-jalan', compact('penjualan'));
    }
}

