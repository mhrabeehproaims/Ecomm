<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use Cachable;
    protected $table = 'tax_schedules';
    protected $primaryKey = 'tax_id';
    protected $fillable = ['tax_name', 'tax_percentage', 'cgst', 'sgst', 'igst', 'status'];

    public function getStatusTextAttribute()
    {
        return $this->status == 0 ? 'Inactive' : 'Active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'tax_schedule_id', 'tax_id');
    }
}
