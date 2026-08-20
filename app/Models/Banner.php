<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'is_active',
        'type', // 'hero' (Banner Utama Atas) atau 'sub' (Sub Banner 1280x420)
    ];
}
