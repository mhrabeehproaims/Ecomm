<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Product extends Model
{
    // use Cachable;

    protected $primaryKey = 'product_id';

    protected $guarded = [];

    protected $appends = ['encrypted_product_id', 'offer', 'offer_price'];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    public function getEncryptedProductIdAttribute()
    {
        return Crypt::encrypt($this->product_id);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id', 'product_id')->orderByRaw('CASE WHEN is_primary = 1 THEN 0 ELSE 1 END')->orderBy('image_id', 'asc');
    }

    public function descriptionImages()
    {
        return $this->hasMany(ProductDescriptionImage::class, 'product_id', 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'categ_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'subCat_id');
    }

    public function getSubCategoryNameAttribute()
    {
        return $this->subCategory->subCat_name ?? '';
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id', 'size_id');
    }

    public function weight()
    {
        return $this->belongsTo(weight::class, 'weight_id', 'weight_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function packageType()
    {
        return $this->belongsTo(PackagingType::class, 'package_type_id', 'package_id');
    }

    public function taxSchedule()
    {
        return $this->belongsTo(Tax::class, 'tax_schedule_id', 'tax_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'product_id', 'product_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id', 'product_id');
    }

    public function scopeActive($query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeForCatalog(Builder $query): Builder
    {
        return $query->active()->with(['category', 'subCategory', 'brand', 'size', 'weight', 'unit', 'packageType', 'images']);
    }

    public function stock()
    {
        return $this->hasMany(StockManagement::class, 'product_id', 'product_id');
    }

    public function featured()
    {
        return $this->hasOne(FeaturedProduct::class, 'product_id', 'product_id');
    }

    public function getFormattedDateAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function comments()
    {
        // Product has many comments through order_items
        return $this->hasManyThrough(
            Comment::class,
            OrderItem::class,
            'product_id',  // Foreign key on order_items table
            'order_item_id',  // Foreign key on comments table
            'product_id',  // Local key on products table
            'order_item_id'  // Local key on order_items table
        )->with(['customer', 'replies.customer']);  // optional, if you want nested replies
    }

    public function getDiscountedPriceAttribute()
    {
        $base_price = $this->price1;
        $discount = $this->discount;
        $discounted_price = 0.0;

        if ($discount > 0) {
            if ($this->discount_type == 'rupee') {
                $discounted_price = $base_price - $discount;
            } elseif ($this->discount_type == 'percentage') {
                $percent = ($discount / 100) * $base_price;
                $discounted_price = $base_price - $percent;
            } else {
                $discounted_price = 0.0;
            }
        } else {
            $discounted_price = $base_price;
        }

        return max($discounted_price, 0);
    }

    public function getCgstAmountAttribute()
    {
        $discounted_price = $this->discounted_price;
        $cgst = $this->taxSchedule?->cgst ?? 0;

        if ($discounted_price && $cgst) {
            return ($cgst / 100) * $discounted_price;
        }

        return 0.0;
    }

    public function getIgstAmountAttribute()
    {
        $discounted_price = $this->discounted_price;
        $igst = $this->taxSchedule?->igst ?? 0;

        if ($discounted_price && $igst) {
            return ($igst / 100) * $discounted_price;
        }

        return 0.0;
    }

    public function getNetPriceAttribute()
    {
        $discounted_price = $this->discounted_price;
        $igst = $this->taxSchedule?->igst ?? 0;
        $cgst = $this->taxSchedule?->cgst ?? 0;
        $net_price = $discounted_price;

        if ($igst) {
            $percent = ($igst / 100) * $discounted_price;
            $net_price = $discounted_price + $percent;
        } elseif ($cgst) {
            $percent = ($igst / 100) * $discounted_price;
            $net_price = $discounted_price + ($percent * 2);
        }

        return $net_price;
    }

    public function getDefaultPriceValueAttribute()
    {
        return match ($this->primary_price) {
            'wholesale' => $this->wholesale,
            'retail' => $this->retail,
            'special_price' => $this->special_price,
            default => $this->mrp ?? 0,
        };
    }

    public function getOfferAttribute()
    {
        return OfferZone::select()
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereJsonContains('applicable_product_ids', (string) $this->product_id)
            ->first();
    }

    public function getOfferPriceAttribute()
    {
        $offer = $this->offer;

        if (! $offer) {
            return null;
        }

        $details = collect($offer->product_details)
            ->where('product_id', (string) $this->product_id)
            ->first();

        return $details['final_price'] ?? null;
    }
}
