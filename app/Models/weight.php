<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class weight extends Model
{
    use Cachable;
    
    protected $primaryKey = 'weight_id';
    protected $fillable = ['weight_name', 'status'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getStatusTextAttribute()
    {
       return $this->status == 1 ? 'Active' : 'Inactive';
    }
        public function products()
    {
        return $this->hasMany(Product::class, 'weight_id', 'weight_id');
    }
}
