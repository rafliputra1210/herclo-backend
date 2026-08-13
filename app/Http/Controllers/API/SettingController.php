<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Mengambil semua pengaturan (Untuk tampilan publik)
    public function index()
    {
        // Mengubah data tabel menjadi format array key-value yang rapi
        $settings = Setting::pluck('value', 'key');
        return response()->json(['data' => $settings]);
    }

    // Menyimpan/Memperbarui pengaturan (Untuk Admin)
    public function update(Request $request)
    {
        $request->validate([
            'use_video_opening' => 'required|string',
        ]);

        // Update atau Buat baru jika belum ada
        Setting::updateOrCreate(
            ['key' => 'use_video_opening'],
            ['value' => $request->use_video_opening]
        );

        return response()->json(['message' => 'Pengaturan berhasil diperbarui']);
    }
}