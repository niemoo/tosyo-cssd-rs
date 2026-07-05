<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStorageRackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'   => ['required', 'exists:hospitals,id'],
            'name'          => ['required', 'string', 'max:255'],
            'code'          => ['required', 'string', 'max:50',
                                Rule::unique('storage_racks', 'code')
                                    ->where('hospital_id', $this->hospital_id)
                                    ->whereNull('deleted_at')],
            'location_desc' => ['nullable', 'string', 'max:255'],
            'capacity'      => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Rumah sakit wajib dipilih.',
            'name.required'        => 'Nama rak wajib diisi.',
            'code.required'        => 'Kode rak wajib diisi.',
            'code.unique'          => 'Kode rak sudah digunakan di rumah sakit ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}