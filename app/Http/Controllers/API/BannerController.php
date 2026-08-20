<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::latest();

        if ($request->has('type') && in_array($request->type, ['hero', 'sub'])) {
            $query->where('type', $request->type);
        }

        $banners = $query->get();
        return response()->json(['data' => $banners]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:hero,sub',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:10240', // Maksimal 10MB
        ]);

        $bannerType = $request->input('type', 'hero');

        // Batas maksimal 10 gambar per tipe banner
        $existingCount = Banner::where('type', $bannerType)->count();
        if ($existingCount >= 10) {
            $typeName = $bannerType === 'hero' ? 'Banner Utama (Hero)' : 'Sub Banner (1280x420)';
            return response()->json([
                'message' => "Batas maksimal 10 gambar untuk {$typeName} telah tercapai (10/10). Hapus banner lama terlebih dahulu."
            ], 422);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');

            $banner = Banner::create([
                'title' => $request->title,
                'link_url' => $request->link_url,
                'type' => $bannerType,
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