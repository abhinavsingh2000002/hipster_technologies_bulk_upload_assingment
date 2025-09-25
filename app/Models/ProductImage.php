<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'upload_id',
        'original_path',
        'variant_256',
        'variant_512',
        'variant_1024',
        'is_primary',
        'checksum'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
