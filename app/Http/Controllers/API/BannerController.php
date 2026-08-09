<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        return response()->json(['data' => $banners]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:10240', // Maksimal 10MB
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');

            $banner = Banner::create([
                'title' => $request->title,
                'link_url' => $request->link_url,
                'image_path' => '/storage/' . $path,
                'is_active' => true,
            ]);

            return response()->json(['message' => 'Banner berhasil diunggah', 'data' => $banner], 201);
        }

        return response()->json(['message' => 'Gagal mengunggah banner'], 400);
    }

    public function updateStatus(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->is_active = $request->is_active;
        $banner->save();

        return response()->json(['message' => 'Status banner diperbarui']);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Hapus file fisik dari storage
        $path = str_replace('/storage/', '', $banner->image_path);
        Storage::disk('public')->delete($path);

        $banner->delete();

        return response()->json(['message' => 'Banner dihapus']);
    }
}