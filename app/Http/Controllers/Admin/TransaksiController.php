<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Services\TransaksiService;
use Exception;

class TransaksiController extends Controller
{
    protected TransaksiService $transaksiService;

    public function __construct(TransaksiService $transaksiService)
    {
        $this->transaksiService = $transaksiService;
    }

    public function index(Request $request)
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('nasabah', function ($nq) use ($search) {
                      $nq->where('nama', 'like', "%{$search}%")
                         ->orWhere('no_rekening', 'like', "%{$search}%");
                  });
            });
        }

        $transaksiList = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('admin.transaksi.index', compact('transaksiList'));
    }

    public function createSetor(Request $request)
    {
        $nasabahList = Nasabah::aktif()->orderBy('nama')->get();
        $jenisSampahList = JenisSampah::active()->with('kategori')->orderBy('nama')->get();
        $selectedNasabahId = $request->nasabah_id;

        return view('admin.transaksi.setor.create', compact('nasabahList', 'jenisSampahList', 'selectedNasabahId'));
    }

    public function storeSetor(Request $request)
    {
        $validated = $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'tanggal_transaksi' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'details' => 'required|array|min:1',
            'details.*.jenis_sampah_id' => 'required|exists:jenis_sampah,id',
            'details.*.berat' => 'required|numeric|min:0.01',
        ]);

        try {
            $transaksi = $this->transaksiService->prosesSetoran($validated);
            return redirect()->route('admin.transaksi.show', $transaksi)->with('success', 'Transaksi setoran sampah berhasil disimpan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses setoran: ' . $e->getMessage());
        }
    }

    public function createTarik(Request $request)
    {
        $nasabahList = Nasabah::aktif()->orderBy('nama')->get();
        $selectedNasabahId = $request->nasabah_id;

        return view('admin.transaksi.tarik.create', compact('nasabahList', 'selectedNasabahId'));
    }

    public function storeTarik(Request $request)
    {
        $validated = $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'nominal' => 'required|numeric|min:1000',
            'tanggal_transaksi' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            $transaksi = $this->transaksiService->prosesPenarikan($validated);
            return redirect()->route('admin.transaksi.show', $transaksi)->with('success', 'Penarikan saldo tabungan berhasil diproses.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['nasabah', 'petugas', 'details.jenisSampah.kategori']);
        return view('admin.transaksi.show', compact('transaksi'));
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load(['nasabah', 'petugas', 'details.jenisSampah.kategori']);
        return view('admin.transaksi.struk', compact('transaksi'));
    }

    public function void(Transaksi $transaksi)
    {
        try {
            $this->transaksiService->voidTransaksi($transaksi);
            return back()->with('warning', 'Transaksi telah dibatalkan (void). Saldo nasabah telah disesuaikan kembali.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}

