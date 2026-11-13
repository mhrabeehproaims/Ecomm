<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use Cachable;

    protected $primaryKey = 'brand_id';
    protected $fillable = ['brand_name', 'image', 'status'];

    protected function scopeLatest()
    {
        return $this->orderBy('created_at', 'desc');
    }

    public function getStatustextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function scopeActive(Builder $query): Builder
    {
       return $query->where('status', 1);
    }
    
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'brand_id');
    }
}
