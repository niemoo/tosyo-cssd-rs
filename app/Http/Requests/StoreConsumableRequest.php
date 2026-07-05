<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsumableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'   => ['required', 'exists:hospitals,id'],
            'category_id'   => ['required', 'exists:consumable_categories,id'],
            'name'          => ['required', 'string', 'max:255'],
            'code'          => ['required', 'string', 'max:50',
                                Rule::unique('consumables', 'code')
                                    ->where('hospital_id', $this->hospital_id)
                                    ->whereNull('deleted_at')],
            'unit'          => ['required', Rule::in(['PCS', 'BOX', 'ROLL', 'LITER'])],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Rumah sakit wajib dipilih.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'name.required'        => 'Nama consumable wajib diisi.',
            'code.required'        => 'Kode consumable wajib diisi.',
            'code.unique'          => 'Kode consumable sudah digunakan di rumah sakit ini.',
            'unit.required'        => 'Satuan wajib dipilih.',
            'unit.in'              => 'Satuan tidak valid.',
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