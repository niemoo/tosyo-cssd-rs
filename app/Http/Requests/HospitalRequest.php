<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HospitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hospitalId = $this->route('hospital')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50', Rule::unique('hospitals', 'code')->ignore($hospitalId)->whereNull('deleted_at')],
            'address'   => ['nullable', 'string', 'max:500'],
            'phone'     => ['nullable', 'digits_between:8,15'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Nama rumah sakit wajib diisi.',
            'code.required'       => 'Kode rumah sakit wajib diisi.',
            'code.unique'         => 'Kode rumah sakit sudah digunakan.',
            'phone.digits_between'=> 'Nomor telepon harus berupa angka, minimal 8 dan maksimal 15 digit.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}