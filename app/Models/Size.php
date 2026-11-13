<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use Cachable;

    protected $primaryKey = "size_id";
    protected $fillable = ['size_name', 'status'];

    public function getStatusTextAttribute()
    {
        return $this->status === 0 ? 'Inactive' : 'Active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
     public function products()
    {
        return $this->hasMany(Product::class, 'size_id', 'size_id');
    }
}
