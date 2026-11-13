<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'order_id', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature', 'amount', 'status', 'meta'];
    protected $casts = ['meta' => 'array'];
}
