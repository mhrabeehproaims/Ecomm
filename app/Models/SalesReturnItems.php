<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItems extends Model
{
    protected $primaryKey = 'return_item_id';
    protected $fillable = ['sales_return_id', 'product_id', 'order_item_id', 'quantity', 'price', 'subtotal'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id', 'return_id');
    }
}
