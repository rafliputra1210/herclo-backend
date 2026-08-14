<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // MENGAMBIL SEMUA PRODUK (Untuk Halaman Publik)
    public function index(Request $request)
    {
        // Memulai query ke tabel Product beserta relasi kategorinya
        $query = \App\Models\Product::with('category');

        // 1. Fitur Pencarian Nama
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 2. Fitur Filter Kategori
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // 3. Fitur Filter/Pengurutan Harga
        if ($request->has('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc'); // Harga Termurah
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc'); // Harga Termahal
            }
        } else {
            // Default: Produk terbaru
            $query->latest();
        }

        $products = $query->get();

        return response()->json([
            'message' => 'Data produk berhasil diambil',
            'data' => $products
        ]);
    }
    // MENGAMBIL DETAIL 1 PRODUK
    public function show($slug)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();

        return response()->json([
            'message' => 'Detail Produk berhasil diambil',
            'data' => $product
        ]);
    }

    // MENAMBAH PRODUK BARU (Hanya untuk Admin)
    // --- Fungsi Tambah Produk (Store) ---
    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'price' => 'required|numeric',
        'stock_quantity' => 'required|integer',
        'description' => 'nullable|string', // <-- Tambahkan baris ini
        'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
    ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        $product = \App\Models\Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description ?? 'Deskripsi standar produk HERCLO.',
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'image_path' => $imagePath,
        ]);

        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    // --- Fungsi Update Produk ---
    public function update(Request $request, $id)
    {
        $product = \App\Models\Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imagePath = $product->image_path; // Simpan path lama sebagai default

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('/storage/', '', $imagePath))) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $imagePath));
            }
            // Upload gambar baru
            $path = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description ?? $product->description ?? 'Deskripsi standar produk HERCLO.',
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'image_path' => $imagePath,
        ]);

        return response()->json(['message' => 'Produk berhasil diperbarui', 'data' => $product]);
    }

    // MENGHAPUS PRODUK (DELETE)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}