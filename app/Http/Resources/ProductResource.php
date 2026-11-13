<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'product_id'      => encrypt($this->product_id),
            'product_name'    => $this->product_name,
            'description'     => $this->description,
            'health_benefit'  => $this->health_benefit,
            'category'        => $this->category?->categ_name ?? "",
            'category_id'     => encrypt($this->category_id),
            'sub_category'    => $this->subCategory?->subCat_name ?? "",
            'sub_category_id' => $this->sub_category_id,
            'stock'           => $this->stock,
            'minimum_stock'   => $this->minimum_stock,
            'brand'           => $this->brand?->brand_name ?? "",
            'brand_id'        => $this->brand_id,
            'size'            => $this->size?->size_name ?? "",
            'size_id'         => $this->size_id,
            'unit'            => $this->unit?->unit_name ?? "",
            'unit_id'         => $this->unit_id,
            'weight'          => $this->weight?->weight_name ?? "",
            'weight_id'       => $this->weight_id,
            'package_type'    => $this->packageType?->package_type ?? "",
            'package_type_id' => $this->package_type_id,
            'mrp'             => $this->mrp,
            'retail'          => $this->retail,
            'wholesale'       => $this->wholesale,
            'special_price'   => $this->special_price,
            'primary_price'   => $this->primary_price,
            'hsn_code'        => $this->hsn_code,

            'primary_image'   => $this->images->where('is_primary', 1)?->first()->image_path
                ?? $this->images->first()?->image_path,
                
            'image_path'      => $this->images ? url('storage/') : '',
            'is_wishlisted' => $this->is_wishlisted ?? false,
            'is_featured' => $this->is_featured ?? false,
            
            'offer' => $this->offer ? [
                'offer_type'     => $this->offer->offer_type,
                'title'          => $this->offer->title,
                'description'    => $this->offer->description,
                'discount_type'  => $this->offer->discount_type,
                'discount_value' => $this->offer->discount_value,
                'discount_from'  => $this->offer->discount_from,
            ] : null,

            'offer_price' => $this->offer_price ?? null,

        ];
    }
}
