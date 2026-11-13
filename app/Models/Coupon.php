<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount','coupon_name',
        'max_uses', 'max_uses_per_user', 'start_date',
        'end_date', 'is_active'
    ];

    // Accessor to check validity
    public function isValid()
    {
        $today = Carbon::today();
        return $this->is_active &&
               (!$this->start_date || $this->start_date <= $today) &&
               (!$this->end_date || $this->end_date >= $today);
    }
}

