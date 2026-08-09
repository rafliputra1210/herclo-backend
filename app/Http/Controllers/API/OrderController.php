<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // Mengambil semua data order beserta nama customer (user)
        $orders = Order::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data pesanan berhasil diambil',
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
}
