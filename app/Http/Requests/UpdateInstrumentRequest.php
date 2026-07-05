<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'     => ['required', 'exists:instrument_categories,id'],
            'name'            => ['required', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'material'        => ['nullable', 'string', 'max:255'],
            'lifespan_cycles' => ['nullable', 'integer', 'min:1'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori instrumen wajib dipilih.',
            'name.required'        => 'Nama instrumen wajib diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}