<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'barcode'   => ['nullable', 'string', 'max:100',
                            Rule::unique('trays', 'barcode')->ignore($this->tray->id)],
            'notes'     => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.instrument_item_id' => ['required', 'exists:instrument_items,id'],
            'items.*.notes'             => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                     => 'Nama tray wajib diisi.',
            'barcode.unique'                    => 'Barcode sudah digunakan.',
            'items.required'                    => 'Tray harus memiliki minimal 1 instrumen.',
            'items.*.instrument_item_id.required' => 'Item instrumen wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}