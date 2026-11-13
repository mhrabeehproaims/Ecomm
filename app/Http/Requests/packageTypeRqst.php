<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class packageTypeRqst extends FormRequest
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
        return [
            'package_name' => 'required|string|unique:packaging_types,package_type',
            'package_status' => 'required|integer'
        ];
    }
}
