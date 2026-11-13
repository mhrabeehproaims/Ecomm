<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAgentPincode extends Model
{
    protected $primaryKey = 'pincode_id';

    protected $fillable = ['delivery_agent_id', 'pincode'];

    public function partner()
    {
        $this->belongsTo(DeliveryPartner::class, 'partner_id', 'delivery_agent_id');
    }
}
