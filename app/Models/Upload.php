<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = ['dzuuid', 'filename', 'size', 'checksum', 'disk', 'path', 'status', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
