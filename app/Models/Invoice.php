<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'invoice_number', 'invoice_date', 'total_amount'];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
