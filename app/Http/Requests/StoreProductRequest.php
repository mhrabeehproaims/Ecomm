<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $catId = $this->input('category');
        return [
            'product_name'   => [
                'required',
                'string',
                'min:5',
                'max:150',
                Rule::unique('products', 'product_name')->where('category_id', $catId)
            ],
            'product_desc'   => 'nullable|string',
            'health_benefit'   => 'nullable|string',
            'category'       => 'required|integer|exists:categories,categ_id',
            'subcategory'    => 'nullable|integer|exists:sub_categories,subCat_id',
            'product_stock'  => 'nullable|integer',
            'minimum_stock'  => 'nullable|integer',
            'product_brand'  => 'required|integer',
            'product_size'   => 'nullable|integer',
            'product_weight'   => 'nullable|numeric',
            'product_unit'   => 'nullable|integer',
            'package_type'   => 'nullable|integer',
            'hsn_code'   => 'nullable|string',
            'product_mrp' => 'required|numeric|min:0',
            'retail_price' => 'nullable|numeric',
            'wholesale_price' => 'nullable|numeric',
            'special_price' => 'required|numeric|lte:product_mrp',
            'discount_type' => 'nullable|in:rupee, percentage',
            'product_discount' => 'nullable|numeric',
            'tax_schedule'     => 'nullable|integer|exists:tax_schedules,tax_id',
            'product_images'   => 'nullable|array',
            'product_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'desc_images'   => 'nullable|array',
            'desc_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'package_weight' => 'nullable|string',
            'primary_price' => 'nullable|string'
        ];
    }
}
