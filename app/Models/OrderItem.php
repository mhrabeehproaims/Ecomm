<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $primaryKey = "order_item_id";
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'total', 'tax_percent', 'cgst_percent', 'sgst_percent', 'igst_percent', 'amount_wo_tax', 'cgst_amount', 'sgst_amount', 'igst_amount', 'actual_price', 'discountPerUnit', 'offer_applied', 'offer_details'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    public function comment()
    {
        return $this->hasMany(Comment::class, 'order_item_id', 'order_item_id');
    }
    public function returned()
    {
        return $this->hasOne(SalesReturnItems::class, 'order_item_id', 'order_item_id');
    }
}
