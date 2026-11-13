<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutEvent extends Model
{
    protected $guarded = [];
    protected $casts = [
        'clicked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }
}
