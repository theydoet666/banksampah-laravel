<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

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
            'alamat' => 'required|string',
            'no_telp' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'tahun_berdiri' => 'nullable|string|max:4',
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}

