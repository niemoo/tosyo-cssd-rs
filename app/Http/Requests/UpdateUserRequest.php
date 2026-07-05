<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'digits_between:8,15'],
            'password'    => ['nullable', 'confirmed', Password::min(8)],
            'is_active'   => ['boolean'],
            'hospital_ids'   => ['required', 'array', 'min:1'],
            'hospital_ids.*' => ['exists:hospitals,id'],
            'role'        => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'hospital_ids.required' => 'Pilih minimal satu rumah sakit.',
            'role.required'         => 'Role wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}