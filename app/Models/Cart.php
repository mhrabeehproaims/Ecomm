<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    // use Cachable;
    protected $primaryKey = 'cart_id';
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'price',
        'options'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'customer_id');
    }
}
