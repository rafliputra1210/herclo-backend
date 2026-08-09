<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->get();
        return response()->json(['data' => $galleries]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:10240', // Maksimal 10MB
        ]);

        if ($request->hasFile('image')) {
            // Simpan file ke folder storage/app/public/galleries
            $path = $request->file('image')->store('galleries', 'public');

            $gallery = Gallery::create([
                'title' => $request->title,
                'category' => $request->category,
                'image_path' => '/storage/' . $path, // Path URL
            ]);

            return response()->json(['message' => 'Gambar berhasil diunggah', 'data' => $gallery], 201);
        }

        return response()->json(['message' => 'Gagal mengunggah gambar'], 400);
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        // Hapus file fisik dari storage
        $path = str_replace('/storage/', '', $gallery->image_path);
        Storage::disk('public')->delete($path);

        $gallery->delete();

        return response()->json(['message' => 'Gambar dihapus']);
    }
}