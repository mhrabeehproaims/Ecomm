<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnPolicy extends Model
{

    protected $primaryKey = "policy_id";

    protected $fillable = [
        'title',
        'return_days',
        'return_type',
        'is_active',
        'conditions',
        'created_by',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
