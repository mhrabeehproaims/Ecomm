<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use Cachable;

    protected $primaryKey = 'order_id';

    // protected $fillable = ['reference_order_id', 'customer_id', 'total_amount', 'discount', 'total_tax_amount', 'status', 'coupon_id', 'coupon_code', 'coupon_discount', 'coupon_discount_type', 'payment_method', 'paid_at', 'delivery_tracking_id', 'delivery_date', 'delivery_charge', 'delivery_agent_id', 'delivery_state'];

    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public function customer()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id', 'order_id');
    }

    public function latestStatus()
    {
        return $this->hasOne(OrderStatusLog::class, 'order_id', 'order_id')->latestOfMany('log_id');
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryPartner::class, 'partner_id', 'delivery_agent_id');
    }

    // public function deliveryStatus()
    // {
    //     $this->belongsTo(DeliveryFollowup::class, 'order_id', 'order_id');
    // }
    public function latestDeliveryStatus()
    {
        return $this->hasOne(DeliveryFollowup::class, 'order_id', 'order_id')->latest();
    }

    public function getFormattedCouponValueAttribute()
    {
        if (!$this->coupon_code || !$this->coupon_discount) {
            return '';
        }

        if ($this->coupon_discount_type === 'fixed') {
            return '₹ ' . number_format($this->coupon_discount, 2);
        }
        return $this->coupon_discount . ' %';
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id')->latest();
    }
}
