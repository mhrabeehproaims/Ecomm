<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    protected $primaryKey = 'log_id';
    protected $fillable = [
        'order_id',
        'status',
        'changed_by',
        'remarks',
        'changed_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // public function changedBy()
    // {
    //     return $this->belongsTo(User::class, 'changed_by');
    // }
}
