<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\Support\RandomPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('unit')->whereIn('role', ['admin', 'pengelola', 'petugas'])->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.users.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,pengelola,petugas',
            'unit_id' => 'required_if:role,petugas|nullable|exists:units,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        if (in_array($validated['role'], ['admin', 'pengelola'])) {
            $validated['unit_id'] = null; // Admin & Pengelola adalah level desa lintas unit
        }

        $role = $validated['role'];
        unset($validated['role']); // hapus role dari array sebelum create (bukan fillable)
        $validated['must_change_password'] = true; // Petugas/pengelola baru wajib ganti password bawaan saat login pertama
        $createdUser = User::createWithRole($validated, $role);

        auditLog(
            action: 'create',
            module: 'Pengguna Sistem',
            description: "Menambahkan pengguna baru: {$createdUser->name} ({$createdUser->email}) dengan role {$role}",
            subject: $createdUser
        );

        return redirect()->route('admin.users.index')->with('success', 'Petugas/Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $units = Unit::aktif()->orderBy('nama')->get();
        return view('admin.users.edit', compact('user', 'units'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,pengelola,petugas',
            'unit_id' => 'required_if:role,petugas|nullable|exists:units,id',
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');
        if (in_array($validated['role'], ['admin', 'pengelola'])) {
            $validated['unit_id'] = null;
        }

        // Lindungi administrator aktif terakhir dari penonaktifan atau penurunan role
        $role = $validated['role'];
        $willRemainActiveAdmin = ($role === 'admin') && $validated['is_active'];
        if ($user->isAdmin() && $user->is_active && ! $willRemainActiveAdmin) {
            $hasOtherActiveAdmin = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->exists();

            if (! $hasOtherActiveAdmin) {
                return back()->withInput()->with('error', 'Tidak dapat menonaktifkan atau menurunkan role administrator aktif terakhir.');
            }
        }

        // role bukan fillable, update via forceFill + update sisanya
        $oldRole = $user->role;
        $oldActive = $user->is_active;
        unset($validated['role']);
        $user->update($validated);
        $user->forceFill(['role' => $role])->save();

        auditLog(
            action: 'update',
            module: 'Pengguna Sistem',
            description: "Memperbarui pengguna {$user->name} ({$user->email}) [Role: {$oldRole} -> {$role}, Aktif: " . ($oldActive ? 'Ya' : 'Tidak') . " -> " . ($user->is_active ? 'Ya' : 'Tidak') . "]",
            subject: $user
        );

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        auditLog(
            action: 'update',
            module: 'Pengguna Sistem',
            description: "Status akun {$user->name} ({$user->email}) {$status}",
            subject: $user
        );

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function resetPassword(User $user)
    {
        $newPassword = RandomPassword::generate(10);
        $user->update([
            'password'            => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        auditLog(
            action: 'reset_password',
            module: 'Pengguna Sistem',
            description: "Mereset kata sandi pengguna {$user->name} ({$user->email})",
            subject: $user
        );

        return back()
            ->with('success', "Password untuk akun {$user->name} berhasil direset.")
            ->with('generated_password', $newPassword);
    }
}

