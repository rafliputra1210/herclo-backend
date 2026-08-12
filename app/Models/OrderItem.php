<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    // Relasi balik ke Pesanan Utama (Order)
    public function order() {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke Produk
    public function product() {
        return $this->belongsTo(Product::class);
    }
}