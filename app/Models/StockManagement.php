<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockManagement extends Model
{
    protected $table = 'stock_management';
    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'product_id',
        'quantity',
        'stock_status',
        'notes',
    ];

    public function getFormattedDateAttribute()
    {
        return  $this->created_at->format('d-m-Y h:i A');
    }
    public function getFormattedStatusAttribute()
    {
        return  $this->stock_status == "in_stock" ? 'In' : 'Out';
    }
}
