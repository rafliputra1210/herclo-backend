<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi input dari Frontend
        $request->validate([
            'email' => 'required|email',
            'dashboard_code' => 'required|string|min:4',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // 2. AUTO-REGISTER / CARI USER BERDASARKAN EMAIL
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                // Ekstrak nama depan dari alamat pengiriman (Format: "Nama | Alamat")
                $nameParts = explode(' | ', $request->shipping_address);
                $name = $nameParts[0] ?? 'Pelanggan HERCLO';

                $user = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    // Enkripsi "Kode Dashboard" menjadi password permanen
                    'password' => Hash::make($request->dashboard_code), 
                ]);
            } else {
                // Opsional: Jika pelanggan lama lupa password, kita perbarui dengan kode yang baru mereka ketik
                $user->update([
                    'password' => Hash::make($request->dashboard_code)
                ]);
            }

            // Gunakan ID user (baik yang baru dibuat maupun yang sudah ada)
            $userId = $user->id;
            $totalAmount = 0;
            
            // 3. Hitung total harga dan validasi ketersediaan stok
            foreach ($request->items as $item) {
                $product = Product::find($item['product']['id']);
                if (!$product) {
                    throw new \Exception('Salah satu produk tidak ditemukan dalam katalog.');
                }
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception('Stok produk ' . $product->name . ' tidak mencukupi.');
                }
                $totalAmount += $product->price * $item['quantity'];
            }

            // 4. Buat Record Pesanan Utama
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Status awal selalu pending
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
            ]);

            // 5. Simpan Detail Barang dan Kurangi Stok di Database
            foreach ($request->items as $item) {
                $product = Product::find($item['product']['id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'size' => $item['size'] ?? '-',
                    'color' => $item['color'] ?? '-',
                ]);
                // Kurangi stok produk secara real-time
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // 6. KONFIGURASI MIDTRANS
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Nama pembeli untuk dikirim ke invoice Midtrans
            $firstName = explode(' | ', $request->shipping_address)[0] ?? $user->name;

            $params = [
                'transaction_details' => [
                    // Memberikan kode unik berakhiran timestamp agar tidak terjadi duplikasi ID saat testing
                    'order_id' => 'HERCLO-' . $order->id . '-' . time(), 
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $firstName,
                    'email' => $user->email, 
                ]
            ];

            // 7. Generate Snap Token dari server Midtrans
            $snapToken = Snap::getSnapToken($params);
            
            // 8. Simpan token tersebut ke tabel orders
            $order->update(['snap_token' => $snapToken]);

            DB::commit();
            
            return response()->json([
                'message' => 'Pesanan berhasil diproses!', 
                'order_id' => $order->id,
                'snap_token' => $snapToken 
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


    /**
     * FUNGSI 2: WEBHOOK CALLBACK DARI MIDTRANS (Notifikasi Lunas)
     */
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        
        // Verifikasi keaslian notifikasi menggunakan standar SHA512 Midtrans
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        // Pastikan request ini benar-benar datang dari Midtrans
        if ($hashed == $request->signature_key) {
            
            // Ekstrak ID asli pesanan dari format 'HERCLO-ID-Timestamp'
            $orderIdParts = explode('-', $request->order_id);
            $realOrderId = $orderIdParts[1] ?? null; 
            
            $order = Order::find($realOrderId);
            
            if ($order) {
                // Jika pembayaran berhasil masuk / lunas
                if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                    $order->update(['status' => 'paid']);
                } 
                // Jika pembayaran gagal, kedaluwarsa, atau dibatalkan oleh pengguna
                elseif ($request->transaction_status == 'cancel' || $request->transaction_status == 'deny' || $request->transaction_status == 'expire') {
                    $order->update(['status' => 'cancelled']);
                }
            }
            return response()->json(['message' => 'Notifikasi Midtrans berhasil diproses.']);
        }
        
        return response()->json(['message' => 'Akses ditolak. Signature tidak valid.'], 403);
    }
    public function myOrders(Request $request)
    {
        $orders = \App\Models\Order::with(['items.product'])
                    ->where('user_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return response()->json(['data' => $orders]);
    }
}