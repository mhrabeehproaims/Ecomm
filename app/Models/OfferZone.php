<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferZone extends Model
{
    protected $fillable = [
        'offer_type',
        'title',
        'description',
        'discount_from',
        'discount_type',
        'discount_value',

        'category_ids',
        'subcategory_ids',
        'brand_ids',

        'applicable_product_ids',
        'product_details',

        'buy_qty',
        'get_qty',
        'free_shipping',
        'start_date',
        'end_date',
        'status',
    ];


    protected $casts = [
        'category_ids' => 'array',
        'subcategory_ids' => 'array',
        'brand_ids' => 'array',
        'applicable_product_ids' => 'array',
        'product_details' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];


    public function isActive(): bool
    {
        $now = now();
        if ($this->status != 1) return false;
        if ($this->start_date && $now->lt($this->start_date)) return false;
        if ($this->end_date && $now->gt($this->end_date)) return false;
        return true;
    }
}
