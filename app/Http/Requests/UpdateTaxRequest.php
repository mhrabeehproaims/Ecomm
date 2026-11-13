<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
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
        $id = $this->route('tax_master');
        return [
            'tax_name' => ['required', 'string', Rule::unique('tax_schedules', 'tax_name')->ignore($id, 'tax_id')],
            'tax_percent'    => 'required|numeric|min:0|max:100',
            'tax_cgst'       => 'nullable|numeric|min:0|max:100',
            'tax_sgst'       => 'nullable|numeric|min:0|max:100',
            'tax_igst'       => 'nullable|numeric|min:0|max:100',
            'tax_status'     => 'required|in:0,1',
        ];
    }
}
