<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $primaryKey = "banner_id";
    protected $fillable = [
        'banner_image',
        'banner_title',
        'banner_subtitle',
        'button_text',
        'button_link',
        'reference_type',
        'reference_id',
        'button_position_type',
        'preset_position',
        'button_x_position',
        'button_y_position',
        'from_date',
        'to_date',
        'banner_status',
        'sort_order',
        'button_color',
        'button_text_color',
        'button_font_size',
        'button_shape'
    ];

    public function getStatusTextAttribute()
    {
        return $this->banner_status == 1 ? "Active" : "Inactive";
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'reference_id', 'product_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'reference_id', 'categ_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'reference_id', 'subCat_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'reference_id', 'brand_id');
    }
}
