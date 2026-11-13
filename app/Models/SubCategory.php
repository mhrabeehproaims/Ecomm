<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    // use Cachable;

    protected $primaryKey = "subCat_id";
    protected $fillable = ['cat_id', 'subCat_name', 'subcateg_desc', 'return_days', 'status', 'image'];

    public function category()
    {
        return $this->hasOne(Category::class, 'categ_id', 'cat_id');
    }

    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? "Active" : "Inactive";
    }

    public function scopeActive($query): Builder
    {
        return $query->where('status', 1);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'subCat_id');
    }
}
