<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsumableUsageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'usageable_type' => ['required', Rule::in(['tray', 'sterilization_batch'])],
            'usageable_id'   => ['required', 'integer'],
            'consumable_id'  => ['required', 'exists:consumables,id'],
            'quantity'       => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:255'],
            'used_at'        => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'consumable_id.required' => 'Pilih consumable yang digunakan.',
            'quantity.required'      => 'Jumlah pemakaian wajib diisi.',
            'quantity.min'           => 'Jumlah minimal 1.',
            'used_at.required'       => 'Tanggal pemakaian wajib diisi.',
        ];
    }
}