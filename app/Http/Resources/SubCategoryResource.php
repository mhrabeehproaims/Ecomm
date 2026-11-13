<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->subCat_id,
            'categoy_id' => encrypt($this->cat_id),
            'sub_cat_name' => $this->subCat_name,
            'image' => $this->image,
            'image_url' => $this->image ? url('storage/subCategImage') : ""
        ];
    }
}
