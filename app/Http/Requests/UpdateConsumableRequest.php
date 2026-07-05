<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsumableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'   => ['required', 'exists:consumable_categories,id'],
            'name'          => ['required', 'string', 'max:255'],
            'unit'          => ['required', Rule::in(['PCS', 'BOX', 'ROLL', 'LITER'])],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'   => 'Kategori wajib dipilih.',
            'name.required'          => 'Nama consumable wajib diisi.',
            'unit.required'          => 'Satuan wajib dipilih.',
            'unit.in'                => 'Satuan tidak valid.',
            'minimum_stock.required' => 'Stok minimum wajib diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}