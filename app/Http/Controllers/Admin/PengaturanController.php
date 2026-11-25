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
            'favicon' => 'nullable|file|mimes:ico,png,svg,webp,jpg,jpeg|max:1024',
            'hapus_favicon' => 'nullable|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // Sanitasi keamanan file SVG dari script berbahaya
            if (in_array(strtolower($file->getClientOriginalExtension()), ['svg']) || $file->getMimeType() === 'image/svg+xml') {
                $svgContent = file_get_contents($file->getRealPath());
                if (preg_match('/<script|onload|onerror|onclick|javascript:|data:/i', $svgContent)) {
                    return back()->withInput()->with('error', 'File logo SVG ditolak karena terdeteksi mengandung script yang berpotensi tidak aman.');
                }
            }

            $oldLogo = AppSetting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            AppSetting::set('logo', $path);
        }

        if ($request->boolean('hapus_favicon')) {
            $oldFavicon = AppSetting::get('favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            AppSetting::set('favicon', null);
        } elseif ($request->hasFile('favicon')) {
            $faviconFile = $request->file('favicon');

            // Sanitasi keamanan favicon jika berupa SVG
            if (in_array(strtolower($faviconFile->getClientOriginalExtension()), ['svg']) || $faviconFile->getMimeType() === 'image/svg+xml') {
                $faviconContent = file_get_contents($faviconFile->getRealPath());
                if (preg_match('/<script|onload|onerror|onclick|javascript:|data:/i', $faviconContent)) {
                    return back()->withInput()->with('error', 'File favicon SVG ditolak karena terdeteksi mengandung script yang berpotensi tidak aman.');
                }
            }

            $oldFavicon = AppSetting::get('favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $path = $request->file('favicon')->store('settings', 'public');
            AppSetting::set('favicon', $path);
        }

        unset($validated['logo'], $validated['favicon'], $validated['hapus_favicon']);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value ?? '');
        }

        auditLog(
            action: 'update',
            module: 'Pengaturan',
            description: "Memperbarui identitas sistem Bank Sampah: {$validated['nama_bank_sampah']}"
        );

        return back()->with('success', 'Pengaturan sistem dan identitas bank sampah berhasil diperbarui.');
    }
}

