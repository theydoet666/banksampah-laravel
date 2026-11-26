<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Unit;
use App\Http\Controllers\Concerns\AuthorizesUnitAccess;
use App\Services\TransaksiService;
use App\Services\PinGuard;
use Exception;

class TransaksiController extends Controller
{
    use AuthorizesUnitAccess;

    protected TransaksiService $transaksiService;

    public function __construct(TransaksiService $transaksiService)
    {
        $this->transaksiService = $transaksiService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Transaksi::with(['nasabah', 'petugas', 'unit']);

        // Scope Petugas ke Unit-nya (Fail-Closed)
        if ($user && $user->isPetugas()) {
            $query = $this->scopeUnitQuery($query, $user);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

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
        $units = Unit::aktif()->orderBy('nama')->get();

        return view('admin.transaksi.index', compact('transaksiList', 'units'));
    }

    public function createSetor(Request $request)
    {
        $nasabahQuery = Nasabah::aktif()->orderBy('nama');
        if (auth()->user()?->isPetugas()) {
            if (empty(auth()->user()->unit_id)) {
                abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
            }
            $nasabahQuery->where('unit_id', auth()->user()->unit_id);
        }
        $nasabahList = $nasabahQuery->get();

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

        $nasabah = Nasabah::findOrFail($validated['nasabah_id']);
        $this->authorizeUnitAccessForNasabah($nasabah);

        try {
            $transaksi = $this->transaksiService->prosesSetoran($validated);
            $routePrefix = auth()->user()->isPetugas() ? 'petugas.' : 'admin.';
            return redirect()->route($routePrefix . 'transaksi.show', $transaksi)->with('success', 'Transaksi setoran sampah berhasil disimpan.');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses transaksi setoran.');
        }
    }

    public function createTarik(Request $request)
    {
        $nasabahQuery = Nasabah::aktif()->orderBy('nama');
        if (auth()->user()?->isPetugas()) {
            if (empty(auth()->user()->unit_id)) {
                abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
            }
            $nasabahQuery->where('unit_id', auth()->user()->unit_id);
        }
        $nasabahList = $nasabahQuery->get();

        $selectedNasabahId = $request->nasabah_id;

        return view('admin.transaksi.tarik.create', compact('nasabahList', 'selectedNasabahId'));
    }

    public function storeTarik(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'nasabah_id' => 'required|exists:nasabah,id',
            'nominal' => 'required|numeric|min:1000',
            'tanggal_transaksi' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ];

        // Non-petugas (admin/pengelola) mencairkan dana secara instan sehingga wajib verifikasi PIN
        if ($user && ! $user->isPetugas()) {
            $rules['pin'] = ['required', 'string', 'digits:6'];
        }

        $validated = $request->validate($rules, [
            'pin.required' => 'PIN Otorisasi wajib diisi untuk mencairkan saldo nasabah.',
            'pin.digits' => 'PIN Otorisasi harus berupa 6 digit angka.',
        ]);

        if ($user && ! $user->isPetugas()) {
            $pinError = PinGuard::check($user, $request->pin);
            if ($pinError) {
                return back()->withInput()->with('error', $pinError);
            }
        }

        $nasabah = Nasabah::findOrFail($validated['nasabah_id']);
        $this->authorizeUnitAccessForNasabah($nasabah);

        try {
            $transaksi = $this->transaksiService->prosesPenarikan($validated);
            $msg = $transaksi->status === 'menunggu_persetujuan'
                ? 'Pengajuan penarikan saldo berhasil dikirim dan sedang menunggu persetujuan pengelola desa.'
                : 'Penarikan saldo tabungan berhasil diproses & dicairkan.';

            $routePrefix = auth()->user()->isPetugas() ? 'petugas.' : 'admin.';
            return redirect()->route($routePrefix . 'transaksi.show', $transaksi)->with('success', $msg);
        } catch (\DomainException | \App\Exceptions\InsufficientBalanceException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses penarikan saldo.');
        }
    }

    public function show(Transaksi $transaksi)
    {
        $this->authorizeUnitAccessForTransaksi($transaksi);

        $transaksi->load(['nasabah.unit', 'petugas', 'unit', 'details.jenisSampah.kategori']);
        return view('admin.transaksi.show', compact('transaksi'));
    }

    public function struk(Transaksi $transaksi)
    {
        $this->authorizeUnitAccessForTransaksi($transaksi);

        $transaksi->load(['nasabah', 'petugas', 'details.jenisSampah.kategori']);
        return view('admin.transaksi.struk', compact('transaksi'));
    }

    public function void(Request $request, Transaksi $transaksi)
    {
        if (! auth()->user() || ! auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Administrator yang berwenang membatalkan transaksi.');
        }

        $request->validate([
            'alasan' => 'nullable|string|max:255',
        ]);

        try {
            $this->transaksiService->voidTransaksi($transaksi, $request->input('alasan'));
            return back()->with('warning', 'Transaksi telah dibatalkan (void). Saldo nasabah telah disesuaikan kembali.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            report($e);
            return back()->with('error', 'Terjadi kesalahan sistem saat membatalkan transaksi. Silakan hubungi tim IT.');
        }
    }
}

