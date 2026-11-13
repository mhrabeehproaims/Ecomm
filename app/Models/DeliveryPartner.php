<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
    protected $primaryKey = 'partner_id';
    protected $fillable = [
        'partner_name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address_line',
        'city',
        'state',
        'country',
        'pincode',
        'tracking_url',
        'per_km_rate',
        'base_charge',
        'status',
        'website',
    ];

    public function pincodes()
    {
        $this->hasMany(DeliveryAgentPincode::class, 'delivery_agent_id', 'partner_id');
    }
}
