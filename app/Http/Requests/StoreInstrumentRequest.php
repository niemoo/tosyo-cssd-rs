<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'      => ['required', 'exists:hospitals,id'],
            'category_id'      => ['required', 'exists:instrument_categories,id'],
            'name'             => ['required', 'string', 'max:255'],
            'code'             => ['required', 'string', 'max:50',
                                Rule::unique('instruments', 'code')
                                    ->where('hospital_id', $this->hospital_id)
                                    ->whereNull('deleted_at')],
            'brand'            => ['nullable', 'string', 'max:255'],
            'material'         => ['nullable', 'string', 'max:255'],
            'lifespan_cycles'  => ['nullable', 'integer', 'min:1'],
            'is_active'        => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Rumah sakit wajib dipilih.',
            'category_id.required' => 'Kategori instrumen wajib dipilih.',
            'name.required'        => 'Nama instrumen wajib diisi.',
            'code.required'        => 'Kode instrumen wajib diisi.',
            'code.unique'          => 'Kode instrumen sudah digunakan di rumah sakit ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}