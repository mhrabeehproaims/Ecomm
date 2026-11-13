<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDescriptionImage extends Model
{
    protected $fillable = ['product_id', 'image_name'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (empty($this->image_name)) {
            return asset('images/placeholder.png');
        }
        if (str_starts_with($this->image_name, 'http://') || str_starts_with($this->image_name, 'https://')) {
            return $this->image_name;
        }
        $path = ltrim($this->image_name, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return asset('storage/' . $path);
    }
}
