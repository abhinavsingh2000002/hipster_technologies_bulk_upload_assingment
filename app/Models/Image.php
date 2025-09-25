<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['product_id', 'upload_id', 'original_path', 'variant_256', 'variant_512', 'variant_1024', 'is_primary', 'meta'];

    protected $casts = [
        'meta' => 'array',
        'is_primary' => 'boolean',
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
