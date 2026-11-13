<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAddress extends Model
{
    protected $primaryKey = "address_id";
    protected $fillable = ['customer_id', 'phone', 'email', 'address_line1', 'address_line2', 'city', 'state', 'country', 'postal_code', 'is_default'];
}
