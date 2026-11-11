<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Exception;

class NasabahTarikController extends Controller
{
    public function index()
    {
        $nasabah = auth()->user()->nasabah;

        if (!$nasabah) {
            abort(403, 'Akun Anda belum terhubung dengan data nasabah.');
        }

        $riwayatPengajuan = Transaksi::where('nasabah_id', $nasabah->id)
            ->where('jenis', 'tarik')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('nasabah.tarik.index', compact('nasabah', 'riwayatPengajuan'));
    }

    public function store(Request $request, TransaksiService $transaksiService)
    {
        $nasabah = auth()->user()->nasabah;

        if (!$nasabah) {
            abort(403, 'Akun Anda belum terhubung dengan data nasabah.');
        }

        $request->validate([
            'nominal' => [
                'required',
                'numeric',
                'min:1000',
                'max:' . $nasabah->saldo_tersedia,
            ],
            'metode_penarikan' => 'required|string|in:tunai_pos,tunai_desa,transfer',
            'catatan' => 'nullable|string|max:255',
        ], [
            'nominal.max' => 'Nominal melebihi saldo tersedia Anda saat ini (' . $nasabah->formatted_saldo_tersedia . ').',
            'nominal.min' => 'Nominal minimal penarikan adalah Rp 1.000.',
        ]);

        try {
            $transaksi = $transaksiService->ajukanPenarikan([
                'nasabah_id' => $nasabah->id,
                'nominal' => $request->nominal,
                'metode_penarikan' => $request->metode_penarikan,
                'catatan' => $request->catatan,
                'tanggal_transaksi' => now()->toDateString(),
            ], auth()->user());

            return back()->with('success', "Pengajuan penarikan sebesar Rp " . number_format($transaksi->total_nilai, 0, ',', '.') . " berhasil dikirim! Mohon menunggu persetujuan pengelola desa.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
