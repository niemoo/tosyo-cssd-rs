<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsumableMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'    => ['required', 'exists:hospitals,id'],
            'consumable_id'  => ['required', 'exists:consumables,id'],
            'type'           => ['required', Rule::in(['IN', 'OUT'])],
            'quantity'       => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'moved_at'       => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required'   => 'Rumah sakit wajib dipilih.',
            'consumable_id.required' => 'Consumable wajib dipilih.',
            'type.required'          => 'Tipe pergerakan wajib dipilih.',
            'type.in'                => 'Tipe pergerakan tidak valid.',
            'quantity.required'      => 'Jumlah wajib diisi.',
            'quantity.min'           => 'Jumlah minimal 1.',
            'moved_at.required'      => 'Tanggal pergerakan wajib diisi.',
        ];
    }
}