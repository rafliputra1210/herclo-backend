<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        // Mengambil semua pesanan, digabungkan dengan data user, order_items, dan detail produknya
        $orders = \App\Models\Order::with(['user', 'orderItems.product'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return response()->json([
            'message' => 'Data pesanan berhasil diambil',
            'data' => $orders
        ]);
    }
    public function myOrders(Request $request)
    {
        $user = $request->user();
        
        $orders = \App\Models\Order::with('orderItems.product')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return response()->json([
            'message' => 'Riwayat pesanan berhasil diambil',
            'data' => $orders
        ]);
    }
    // Endpoint untuk update status pesanan (misal dari 'dibayar' jadi 'dikirim')
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }
    public function store(Request $request)
    {
        // 1. Validasi Input dari Frontend
        $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user(); // Bisa null jika guest
        
        if (empty($request->items)) {
            return response()->json(['message' => 'Keranjang belanja kosong'], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $totalAmount = 0;

            // 2. Cek Stok dan Hitung Total Harga
            foreach ($request->items as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception('Stok ' . $product->name . ' tidak mencukupi.');
                }
                $totalAmount += $product->price * $item['quantity'];
            }

            // 3. Simpan Pesanan ke Tabel Orders
            $order = \App\Models\Order::create([
                'user_id' => $user ? $user->id : null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method, // Simpan metode pembayaran
            ]);

            // 4. Pindahkan isi Keranjang ke Detail Pesanan (OrderItems)
            foreach ($request->items as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'size' => $item['size'] ?? '-',     // Simpan Varian Ukuran
                    'color' => $item['color'] ?? '-',   // Simpan Varian Warna
                ]);

                // Kurangi stok produk secara otomatis
                $product->decrement('stock_quantity', $item['quantity']);
            }

            \Illuminate\Support\Facades\DB::commit();
            
            // Kembalikan Response Sukses beserta ID Pesanan untuk mencetak Struk
            return response()->json([
                'message' => 'Pesanan berhasil dibuat!', 
                'order_id' => $order->id
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}