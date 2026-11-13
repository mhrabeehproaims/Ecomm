<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => encrypt($this->categ_id),
            'name' => $this->categ_name,
            'image' => $this->image ?? "",
            'image_url' => $this->image ? url('storage/categoryImage/') : ""
        ];
    }
}
