<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
                              Rule::unique('units', 'code')
                                  ->where('hospital_id', $this->hospital_id)
                                  ->whereNull('deleted_at')],
            'type'        => ['nullable', 'string', 'max:100'],
            'phone'       => ['nullable', 'digits_between:8,15'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Rumah sakit wajib dipilih.',
            'name.required'        => 'Nama unit wajib diisi.',
            'code.required'        => 'Kode unit wajib diisi.',
            'code.unique'          => 'Kode unit sudah digunakan di rumah sakit ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}