<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\User;
use App\Models\Unit;
use App\Models\Transaksi;
use App\Http\Controllers\Concerns\AuthorizesUnitAccess;
use App\Support\RandomPassword;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class NasabahController extends Controller
{
    use AuthorizesUnitAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Nasabah::with(['petugasPendaftar', 'user', 'unit']);

        // Scope Petugas ke Unit-nya sendiri (Fail-Closed via Trait)
        if ($user && $user->isPetugas()) {
            $query = $this->scopeUnitQuery($query, $user);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nasabahList = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $units = Unit::aktif()->orderBy('nama')->get();

        return view('admin.nasabah.index', compact('nasabahList', 'units'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && $user->isPetugas() && empty($user->unit_id)) {
            abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
        }

        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.nasabah.create', compact('units'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->isPetugas() && empty($user->unit_id)) {
            abort(403, 'Akses ditolak: Akun petugas belum ditugaskan ke unit bank sampah.');
        }

        $rules = [
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|digits:16|unique:nasabah,nik',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20|unique:nasabah,no_hp',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
            'status' => 'required|in:aktif,nonaktif',
        ];

        if ($user && $user->isPetugas()) {
            $rules['unit_id'] = 'nullable';
        } else {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        if ($user && $user->isAdmin()) {
            $rules['saldo_awal'] = 'nullable|numeric|min:0';
            $rules['catatan_saldo_awal'] = 'nullable|string|max:255';
            $rules['tanggal_saldo_awal'] = 'nullable|date|before_or_equal:today';
        }

        $validated = $request->validate($rules);

        if ($user && $user->isPetugas()) {
            $validated['unit_id'] = $user->unit_id;
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('nasabah', 'public');
        }

        $noRekening = Nasabah::generateNoRekening();
        $validated['no_rekening'] = $noRekening;
        $validated['registered_by'] = auth()->id();
        
        $saldoAwal = ($user && $user->isAdmin()) ? (float) $request->input('saldo_awal', 0) : 0;
        $validated['saldo_awal'] = $saldoAwal;
        $validated['saldo'] = $saldoAwal;

        // Password awal acak 10 karakter bebas ambigu & paksa ganti password saat login
        $defaultPassword = RandomPassword::generate(10);
        $generatedEmail = strtolower(str_replace('-', '', $noRekening)) . '@nasabah.banksampah.test';

        DB::transaction(function () use ($validated, $defaultPassword, $generatedEmail, $saldoAwal, $request, $user) {
            $userNasabah = User::createWithRole([
                'name'                => $validated['nama'],
                'email'               => $generatedEmail,
                'password'            => Hash::make($defaultPassword),
                'is_active'           => true,
                'unit_id'             => $validated['unit_id'] ?? null,
                'must_change_password' => true,
            ], 'nasabah');

            $validated['user_id'] = $userNasabah->id;
            $nasabah = Nasabah::create($validated);

            if ($saldoAwal > 0) {
                Transaksi::create([
                    'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                    'unit_id' => $nasabah->unit_id,
                    'nasabah_id' => $nasabah->id,
                    'jenis' => 'setor',
                    'total_berat' => null,
                    'total_nilai' => $saldoAwal,
                    'catatan' => $request->input('catatan_saldo_awal') ?: 'Saldo awal migrasi pembukuan manual',
                    'status' => 'aktif',
                    'is_saldo_awal' => true,
                    'petugas_id' => $user->id,
                    'tanggal_transaksi' => $request->input('tanggal_saldo_awal') ?: now()->toDateString(),
                ]);
            }

            auditLog(
                action: 'create',
                module: 'Nasabah',
                description: "Mendaftarkan nasabah baru: {$nasabah->nama} ({$nasabah->no_rekening})" . ($saldoAwal > 0 ? " dengan Saldo Awal " . formatRupiah($saldoAwal) : ""),
                subject: $nasabah
            );
        });

        $msg = "Nasabah baru berhasil didaftarkan! No. Rekening: {$noRekening}. Akun login mandiri dibuat otomatis.";

        $route = auth()->user()?->isPetugas() ? 'petugas.nasabah.index' : 'admin.nasabah.index';
        return redirect()->route($route)
            ->with('success', $msg)
            ->with('generated_password', $defaultPassword);
    }

    public function show(Nasabah $nasabah)
    {
        $this->authorizeUnitAccessForNasabah($nasabah);

        $nasabah->load(['petugasPendaftar', 'unit', 'transaksi' => function ($q) {
            $q->orderBy('id', 'desc')->limit(10);
        }]);

        return view('admin.nasabah.show', compact('nasabah'));
    }

    public function edit(Nasabah $nasabah)
    {
        $this->authorizeUnitAccessForNasabah($nasabah);

        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.nasabah.edit', compact('nasabah', 'units'));
    }

    public function update(Request $request, Nasabah $nasabah)
    {
        $this->authorizeUnitAccessForNasabah($nasabah);

        $user = auth()->user();

        $rules = [
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|digits:16|unique:nasabah,nik,' . $nasabah->id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20|unique:nasabah,no_hp,' . $nasabah->id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
            'status' => 'required|in:aktif,nonaktif',
        ];

        if ($user && $user->isAdmin()) {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('foto')) {
            if ($nasabah->foto && Storage::disk('public')->exists($nasabah->foto)) {
                Storage::disk('public')->delete($nasabah->foto);
            }
            $validated['foto'] = $request->file('foto')->store('nasabah', 'public');
        }

        $nasabah->update($validated);

        auditLog(
            action: 'update',
            module: 'Nasabah',
            description: "Memperbarui profil nasabah: {$nasabah->nama} ({$nasabah->no_rekening})",
            subject: $nasabah
        );

        $route = auth()->user()?->isPetugas() ? 'petugas.nasabah.show' : 'admin.nasabah.show';
        return redirect()->route($route, $nasabah)->with('success', 'Data nasabah berhasil diperbarui.');
    }

    public function toggleStatus(Nasabah $nasabah)
    {
        $this->authorizeUnitAccessForNasabah($nasabah);

        $newStatus = $nasabah->status === 'aktif' ? 'nonaktif' : 'aktif';
        $nasabah->update(['status' => $newStatus]);

        return back()->with('success', "Status nasabah {$nasabah->nama} diubah menjadi {$newStatus}.");
    }

    public function resetPassword(Nasabah $nasabah)
    {
        $this->authorizeUnitAccessForNasabah($nasabah);

        // Password acak 10 karakter bebas ambigu & paksa ganti password
        $defaultPassword = RandomPassword::generate(10);

        if ($nasabah->user) {
            // Hapus is_active => true agar nasabah nonaktif tidak aktif kembali tanpa izin
            $nasabah->user->update([
                'password'            => Hash::make($defaultPassword),
                'must_change_password' => true, // paksa ganti password setelah reset
            ]);
        } else {
            // Jika belum punya akun user, buatkan seketika
            $generatedEmail = strtolower(str_replace('-', '', $nasabah->no_rekening)) . '@nasabah.banksampah.test';
            $user = User::createWithRole([
                'name'                => $nasabah->nama,
                'email'               => $generatedEmail,
                'password'            => Hash::make($defaultPassword),
                'is_active'           => $nasabah->status === 'aktif',
                'unit_id'             => $nasabah->unit_id,
                'must_change_password' => true,
            ], 'nasabah');
            $nasabah->update(['user_id' => $user->id]);
        }

        auditLog(
            action: 'reset_password',
            module: 'Nasabah',
            description: "Mereset kata sandi nasabah: {$nasabah->nama} ({$nasabah->no_rekening})",
            subject: $nasabah
        );

        return back()
            ->with('success', "Password akun nasabah {$nasabah->nama} berhasil direset.")
            ->with('generated_password', $defaultPassword);
    }

    public function inputSaldoAwal(Request $request, Nasabah $nasabah)
    {
        $user = auth()->user();
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Administrator yang berwenang menambahkan saldo awal.');
        }

        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1',
            'tanggal_transaksi' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:255',
        ]);

        $nominal = (float) $validated['nominal'];
        $catatan = !empty($validated['catatan']) ? $validated['catatan'] : 'Saldo awal migrasi pembukuan manual';

        DB::transaction(function () use ($nasabah, $nominal, $catatan, $validated, $user) {
            $lockedNasabah = Nasabah::lockForUpdate()->findOrFail($nasabah->id);
            if ($lockedNasabah->status !== 'aktif') {
                abort(422, 'Nasabah berstatus nonaktif tidak dapat ditambahkan saldo awal.');
            }

            $lockedNasabah->increment('saldo', $nominal);
            $lockedNasabah->increment('saldo_awal', $nominal);

            Transaksi::create([
                'kode_transaksi' => Transaksi::generateKodeTransaksi(),
                'unit_id' => $lockedNasabah->unit_id,
                'nasabah_id' => $lockedNasabah->id,
                'jenis' => 'setor',
                'total_berat' => null,
                'total_nilai' => $nominal,
                'catatan' => $catatan,
                'status' => 'aktif',
                'is_saldo_awal' => true,
                'petugas_id' => $user->id,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
            ]);

            auditLog(
                action: 'saldo_awal',
                module: 'Transaksi Warga',
                description: "Menambahkan saldo awal migrasi " . formatRupiah($nominal) . " untuk nasabah {$lockedNasabah->nama} ({$lockedNasabah->no_rekening})",
                subject: $lockedNasabah
            );
        });

        return back()->with('success', "Saldo awal migrasi sebesar " . formatRupiah($nominal) . " berhasil ditambahkan untuk nasabah {$nasabah->nama}.");
    }
}

