<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    // --- UNTUK ADMIN: Mengambil semua data promo ---
    public function index()
    {
        return response()->json(['data' => Promo::latest()->get()]);
    }

    // --- UNTUK ADMIN: Membuat promo baru ---
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:promos',
            'type' => 'required|in:nominal,persen',
            'value' => 'required|numeric',
            'min_purchase' => 'required|numeric'
        ]);
        Promo::create($request->all());
        return response()->json(['message' => 'Promo berhasil ditambahkan']);
    }

    // --- UNTUK ADMIN: Menghapus promo ---
    public function destroy($id)
    {
        Promo::destroy($id);
        return response()->json(['message' => 'Promo dihapus']);
    }

    // --- UNTUK PUBLIK: Memvalidasi Kode Promo di halaman Checkout ---
    public function validatePromo(Request $request)
    {
        $promo = Promo::where('code', $request->code)->where('is_active', true)->first();

        if (!$promo) {
            return response()->json(['message' => 'Kode promo tidak ditemukan atau sudah tidak aktif'], 404);
        }

        if ($request->total_amount < $promo->min_purchase) {
            return response()->json(['message' => 'Minimal belanja untuk promo ini adalah Rp ' . number_format($promo->min_purchase, 0, ',', '.')], 400);
        }

        // Hitung nominal diskon yang didapat
        $discountAmount = 0;
        if ($promo->type == 'nominal') {
            $discountAmount = $promo->value;
        } else {
            $discountAmount = ($promo->value / 100) * $request->total_amount;
        }

        return response()->json([
            'message' => 'Kode promo berhasil digunakan!',
            'discount_amount' => $discountAmount,
            'promo_code' => $promo->code
        ]);
    }

    // --- UNTUK PUBLIK: Mengambil promo aktif untuk dipasang di Checkout ---
    public function activePromos()
    {
        return response()->json(['data' => Promo::where('is_active', true)->latest()->get()]);
    }
}