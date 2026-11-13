<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    // use Cachable;
    protected $primaryKey = 'image_id';
    protected $fillable = ['product_id', 'image_path', 'is_primary'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (empty($this->image_path)) {
            return asset('images/placeholder.png');
        }
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }
        $path = ltrim($this->image_path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return asset('storage/' . $path);
    }
}
