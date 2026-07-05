<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSterilizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'          => ['required', 'exists:hospitals,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'code'                 => ['required', 'string', 'max:50',
                                       Rule::unique('sterilizers', 'code')
                                           ->where('hospital_id', $this->hospital_id)
                                           ->whereNull('deleted_at')],
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
            'hospital_id.required'         => 'Rumah sakit wajib dipilih.',
            'name.required'                => 'Nama sterilizer wajib diisi.',
            'code.required'                => 'Kode sterilizer wajib diisi.',
            'code.unique'                  => 'Kode sterilizer sudah digunakan di rumah sakit ini.',
            'type.required'                => 'Tipe sterilizer wajib dipilih.',
            'type.in'                      => 'Tipe sterilizer tidak valid.',
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