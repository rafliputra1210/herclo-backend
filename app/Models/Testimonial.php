<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'customer_name',
        'content',
        'rating',
        'is_featured',
    ];
}
