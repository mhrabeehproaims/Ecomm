<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    use Cachable;
    protected $primaryKey = "package_id";
    protected $fillable = ["package_type", "status"];

    public function  getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'package_type_id', 'package_id');
    }
}
