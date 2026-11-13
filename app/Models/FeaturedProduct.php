<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedProduct extends Model
{
    protected $primaryKey = "featured_id";
    protected $fillable = ['product_id', 'status'];

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'product_id', 'product_id');
    }
}
