<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
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
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        if (in_array($validated['role'], ['admin', 'pengelola'])) {
            $validated['unit_id'] = null; // Admin & Pengelola adalah level desa lintas unit
        }

        $role = $validated['role'];
        unset($validated['role']); // hapus role dari array sebelum create (bukan fillable)
        User::createWithRole($validated, $role);

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
            'unit_id' => 'nullable|exists:units,id',
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

        // role bukan fillable, update via forceFill + update sisanya
        $role = $validated['role'];
        unset($validated['role']);
        $user->update($validated);
        $user->forceFill(['role' => $role])->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function resetPassword(User $user)
    {
        $newPassword = \Illuminate\Support\Str::random(10);
        $user->update([
            'password'            => Hash::make($newPassword),
            'must_change_password' => true,
        ]);
        return back()->with('success', "Password untuk akun {$user->name} direset. Password baru: <strong>{$newPassword}</strong> (catat & sampaikan ke pengguna, wajib diganti saat login).");
    }
}

