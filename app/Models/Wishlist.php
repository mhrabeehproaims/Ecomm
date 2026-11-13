<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use Cachable;

    protected $table = 'wishlists';
    protected $primaryKey = 'wishlist_id';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
