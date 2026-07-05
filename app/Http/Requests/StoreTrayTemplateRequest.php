<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrayTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'              => ['required', 'exists:hospitals,id'],
            'name'                     => ['required', 'string', 'max:255'],
            'code'                     => ['required', 'string', 'max:50',
                                           Rule::unique('tray_templates', 'code')
                                               ->where('hospital_id', $this->hospital_id)
                                               ->whereNull('deleted_at')],
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
            'hospital_id.required'           => 'Rumah sakit wajib dipilih.',
            'name.required'                  => 'Nama template wajib diisi.',
            'code.required'                  => 'Kode template wajib diisi.',
            'code.unique'                    => 'Kode template sudah digunakan di rumah sakit ini.',
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