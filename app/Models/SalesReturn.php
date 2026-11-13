<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use Cachable;
    protected $primaryKey = 'return_id';
    protected $fillable = ['reissued_order_id', 'order_id', 'customer_id', 'status', 'return_date', 'total_amount', 'notes', 'return_reason', 'product_condition', 'reject_reason', 'return_type'];

    public function items()
    {
        return $this->hasMany(SalesReturnItems::class, 'sales_return_id', 'return_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    public function reforder()
    {
        return $this->belongsTo(Order::class, 'reissued_order_id', 'order_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
