<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'    => ['required', 'string', 'max:50', 'unique:users,username'],
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'digits_between:8,15'],
            'password'    => ['required', 'confirmed', Password::min(8)],
            'is_active'   => ['boolean'],
            'hospital_ids'=> ['required', 'array', 'min:1'],
            'hospital_ids.*' => ['exists:hospitals,id'],
            'role'        => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'       => 'Username wajib diisi.',
            'username.unique'         => 'Username sudah digunakan.',
            'name.required'           => 'Nama lengkap wajib diisi.',
            'password.required'       => 'Password wajib diisi.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'password.min'            => 'Password minimal 8 karakter.',
            'hospital_ids.required'   => 'Pilih minimal satu rumah sakit.',
            'role.required'           => 'Role wajib dipilih.',
            'role.exists'             => 'Role tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}