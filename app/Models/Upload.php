<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'uuid',
        'original_name',
        'status',
        'checksum',
        'storage_path',
        'product_id',
        'meta'
    ];
    protected $casts = ['meta' => 'array'];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
    public function productImages()
    {
        return $this->hasMany(\App\Models\ProductImage::class);
    }
}
