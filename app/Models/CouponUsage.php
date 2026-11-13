<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'discount_amount',
        'discount_type',
        'used_at',
        'usage_count'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'id', 'coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
