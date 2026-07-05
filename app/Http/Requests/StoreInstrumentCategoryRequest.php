<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstrumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'exists:hospitals,id'],
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50',
                              Rule::unique('instrument_categories', 'code')
                                  ->where('hospital_id', $this->hospital_id)
                                  ->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Rumah sakit wajib dipilih.',
            'name.required'        => 'Nama kategori wajib diisi.',
            'code.required'        => 'Kode kategori wajib diisi.',
            'code.unique'          => 'Kode kategori sudah digunakan di rumah sakit ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}