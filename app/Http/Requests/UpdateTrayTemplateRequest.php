<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrayTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                     => ['required', 'string', 'max:255'],
            'description'              => ['nullable', 'string', 'max:500'],
            'is_lockable'              => ['boolean'],
            'is_active'                => ['boolean'],
            'items'                    => ['nullable', 'array'],
            'items.*.instrument_id'    => ['required', 'exists:instruments,id'],
            'items.*.quantity'         => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'Nama template wajib diisi.',
            'items.*.instrument_id.required' => 'Instrumen wajib dipilih.',
            'items.*.quantity.required'      => 'Jumlah wajib diisi.',
            'items.*.quantity.min'           => 'Jumlah minimal 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_lockable' => $this->boolean('is_lockable'),
            'is_active'   => $this->boolean('is_active'),
        ]);
    }
}