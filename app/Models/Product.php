<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = ['sku', 'title', 'description', 'price'];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function ensurePrimaryImageFrom(Image $image)
    {
        $lockName = "product:primary:{$this->id}";

        $lock = Cache::lock($lockName, 10);

        return $lock->block(10, function () use ($image) {
            $existing = $this->images()->where('is_primary', true)->first();
            if ($existing) {
                if ($existing->id === $image->id) {
                    return false;
                }
                $existing->update(['is_primary' => false]);
            }
            $image->update(['is_primary' => true]);

            return true;
        });
    }

    public function primaryImage()
    {
        return $this->hasOne(Image::class, 'product_id')
            ->where('is_primary', 1)
            ->select(['id', 'product_id', 'original_path', 'is_primary']);
    }

    protected $appends = ['primary_image_path'];

    public function getPrimaryImagePathAttribute()
    {
        return $this->primaryImage ? $this->primaryImage->original_path : null;
    }
}
