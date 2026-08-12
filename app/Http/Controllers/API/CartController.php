<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Melihat isi keranjang user yang sedang login
    public function index(Request $request)
    {
        $carts = Cart::with('product')->where('user_id', $request->user()->id)->get();
        return response()->json(['data' => $carts]);
    }

    // Menambah produk ke keranjang
    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'size' => 'nullable|string',
        'color' => 'nullable|string'
    ]);

    $product = Product::findOrFail($request->product_id);
    if ($product->stock_quantity < $request->quantity) {
        return response()->json(['message' => 'Stok tidak mencukupi'], 400);
    }

    // Simpan ke keranjang beserta variannya
    $cart = Cart::create([
        'user_id' => $request->user()->id,
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
        'size' => $request->size,
        'color' => $request->color
    ]);

    return response()->json(['message' => 'Produk ditambahkan']);
}
    // Menghapus produk dari keranjang
    public function destroy(Request $request, $id)
    {
        Cart::where('user_id', $request->user()->id)->where('id', $id)->delete();
        return response()->json(['message' => 'Produk dihapus dari keranjang']);
    }
}