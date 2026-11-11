<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_bank_sampah' => 'required|string|max:100',
            'tagline' => 'nullable|string|max:100',
            'alamat' => 'required|string',
            'no_telp' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'tahun_berdiri' => 'nullable|string|max:4',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = AppSetting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            AppSetting::set('logo', $path);
        }

        unset($validated['logo']);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value ?? '');
        }

        return back()->with('success', 'Pengaturan sistem dan identitas bank sampah berhasil diperbarui.');
    }
}

