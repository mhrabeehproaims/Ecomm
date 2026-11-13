<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryFollowup extends Model
{
    protected $fillable = ['order_id', 'note', 'delivery_status'];
    
}

