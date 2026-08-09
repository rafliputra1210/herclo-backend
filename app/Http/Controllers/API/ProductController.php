<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // MENGAMBIL SEMUA PRODUK (Untuk Halaman Publik)
    public function index()
    {
        // Eager loading tabel relasi 'category' agar performa cepat
        $products = Product::with('category')->get();

        return response()->json([
            'message' => 'Daftar Produk berhasil diambil',
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
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Otomatis membuat URL yang SEO-friendly
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Jangan lupa pastikan 'use Illuminate\Support\Str;' ada di atas
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
        ]);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
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