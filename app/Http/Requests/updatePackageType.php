<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class updatePackageType extends FormRequest
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
        $package_id = $this->route('package');
        return [
            'package_name' => ['required', Rule::unique('packaging_types', 'package_type')->ignore($package_id, 'package_id')],
            'package_status' => 'required|integer'
        ];
    }
}
