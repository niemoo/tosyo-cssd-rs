<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSterilizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'type'                 => ['required', Rule::in(['STEAM', 'PLASMA', 'EO'])],
            'capacity'             => ['nullable', 'integer', 'min:1'],
            'serial_number'        => ['nullable', 'string', 'max:100'],
            'last_maintenance_at'  => ['nullable', 'date'],
            'next_maintenance_at'  => ['nullable', 'date', 'after_or_equal:last_maintenance_at'],
            'is_active'            => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama sterilizer wajib diisi.',
            'type.required'  => 'Tipe sterilizer wajib dipilih.',
            'type.in'        => 'Tipe sterilizer tidak valid.',
            'next_maintenance_at.after_or_equal' => 'Jadwal maintenance berikutnya harus setelah maintenance terakhir.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}