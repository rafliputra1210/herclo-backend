<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // MENGAMBIL SEMUA PRODUK (Untuk Halaman Publik)
    public function index(Request $request)
    {
        // Memulai query ke tabel Product beserta relasi kategorinya
        $query = Product::with(['category', 'items', 'variants']);

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
            } else {
                $query->latest();
            }
        } else {
            // Default: Produk terbaru
            $query->latest();
        }

        $products = $query->get();
        return response()->json(['data' => $products]);  
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
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer|min:1', // Pastikan minimal 1 agar barcode bisa digenerate
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'size' => 'required|string',
            'color' => 'required|string',
        ]);

        // 2. Proses Upload Gambar
        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        // 3. Simpan Data Produk Induk
        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description ?? 'Deskripsi standar produk HERCLO.',
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'image_path' => $imagePath,
            'color' => $request->color,
        ]);

        // 4. Proses Pembuatan Hangtag/Barcode (Product Items)
        // Ambil kode dari tabel kategori (misal: "0016")
        $category = Category::find($request->category_id);
        $categoryCode = $category->code ? $category->code : '0000'; // Default jika kode kategori belum diisi

        $items = [];
        
        // Cari ID terakhir dari ProductItem untuk meneruskan nomor urut (001, 002, dst)
        $lastItem = ProductItem::latest('id')->first();
        $startNumber = $lastItem ? $lastItem->id : 0;

        // Looping sebanyak jumlah stok yang diinputkan
        for ($i = 1; $i <= $request->stock_quantity; $i++) {
            
            // Membuat urutan 3 digit (001, 002, 003...)
            $runningNumber = str_pad($startNumber + $i, 3, '0', STR_PAD_LEFT);
            
            // Merakit format akhir: HRC-0016-001
            $serialNumber = "HRC-{$categoryCode}-{$runningNumber}";

            $items[] = [
                'product_id' => $product->id,
                'size' => $request->size,
                'serial_number' => $serialNumber,
                'is_sold' => false, // Status awal: belum terjual
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Masukkan semua data barcode ke database sekaligus agar proses sangat cepat
        if (count($items) > 0) {
            ProductItem::insert($items);
        }

        // 5. Kembalikan Response Sukses
        return response()->json([
            'message' => 'Produk dan ' . $request->stock_quantity . ' Barcode berhasil digenerate!', 
            'data' => $product
        ], 201);
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
            'color' => 'nullable|string',
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
            'color' => $request->has('color') ? $request->color : $product->color,
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