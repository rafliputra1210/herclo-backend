<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        
        return response()->json([
            'message' => 'Daftar Kategori berhasil diambil',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            // Validasi unique mengecualikan ID yang sedang diedit
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category
        ]);
    }

    // MENGHAPUS KATEGORI (DELETE)
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}