<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'sku' => 'required|string',
            'action' => 'required|in:in,out' // 'in' untuk masuk, 'out' untuk keluar
        ]);

        // Kita asumsikan format QR-nya adalah "PRD-1" (di mana 1 adalah ID Produk)
        // Ekstrak ID dari string SKU
        $id = str_replace('PRD-', '', $request->sku);
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan!'], 404);
        }

        // Proses penambahan atau pengurangan stok
        if ($request->action === 'in') {
            $product->stock_quantity += 1;
            $pesan = "Stok {$product->name} berhasil DITAMBAH.";
        } else {
            if ($product->stock_quantity <= 0) {
                return response()->json(['message' => "Gagal! Stok {$product->name} sudah kosong."], 400);
            }
            $product->stock_quantity -= 1;
            $pesan = "Stok {$product->name} berhasil DIKURANGI.";
        }

        $product->save();

        return response()->json([
            'message' => $pesan,
            'current_stock' => $product->stock_quantity,
            'product_name' => $product->name
        ]);
    }
}