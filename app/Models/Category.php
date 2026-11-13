<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // use Cachable;

    protected $primaryKey = 'categ_id';
    protected $fillable = ['categ_name', 'categ_desc', 'return_days', 'status', 'image'];

    public function getStatustextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'categ_id');
    }
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'cat_id', 'categ_id');
    }
}
