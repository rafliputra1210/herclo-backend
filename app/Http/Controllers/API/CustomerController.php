<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        // Mengambil semua data user (customer)
        // withCount untuk menghitung jumlah pesanan
        // withSum untuk menjumlahkan total belanja dari relasi orders
        $customers = User::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $customers]);
    }
}