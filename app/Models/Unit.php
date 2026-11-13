<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
     use Cachable;
    protected $primaryKey = 'unit_id';
    protected $fillable = ['unit_name', 'status'];

    // public function

    public function getStatustextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    protected function scopeLatest()
    {
        return $this->orderBy('created_at', 'desc');
    }

    
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id', 'unit_id');
    }
}
