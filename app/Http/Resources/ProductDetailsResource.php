<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'description' => $this->description,
            'health_benefit' => $this->health_benefit,
            'category' => $this->category?->categ_name ?? "",
            'category_id' => $this->description,
            'sub_category' => $this->subCategory?->subCat_name ?? "",
            'sub_category_id' => $this->sub_category_id,
            'stock' => $this->stock,
            'minimum_stock' => $this->minimum_stock,
            'brand' => $this->brand?->brand_name ?? "",
            'brand_id' => $this->brand_id,
            'size' => $this->size?->size_name ?? "",
            'size_id' => $this->size_id,
            'unit' => $this->unit?->unit_name ?? "",
            'unit_id' => $this->unit_id,
            'weight' => $this->weight?->weight_name ?? "",
            'weight_id' => $this->weight_id,
            'package_type' => $this->packageType?->package_type ?? "",
            'package_type_id' => $this->package_type_id,
            'mrp' => $this->mrp,
            'retail' => $this->retail,
            'wholesale' => $this->wholesale,
            'special_price' => $this->special_price,
            'primary_price' => $this->primary_price,
            'hsn_code' => $this->hsn_code,
            'description' => $this->description,
            'primary_image' => $this->images->where('is_primary', 1)?->first()->image_path ?? $this->images->first()?->image_path,
            'images'        => $this->images->pluck('image_path')->toArray(),
            'Description_images' => $this->descriptionImages->pluck('image_name')->toArray(),
            'image_path' => $this->images ? url('storage') : '',
        ];
    }
}
