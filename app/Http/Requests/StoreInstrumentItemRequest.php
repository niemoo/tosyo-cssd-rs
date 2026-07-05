<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstrumentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'   => ['required', 'exists:hospitals,id'],
            'instrument_id' => ['required', 'exists:instruments,id'],
            'code'          => ['required', 'string', 'max:50', Rule::unique('instrument_items', 'code')->whereNull('deleted_at')],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'serial_number')->whereNull('deleted_at')],
            'barcode'       => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'barcode')->whereNull('deleted_at')],
            'rfid_tag'      => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'rfid_tag')->whereNull('deleted_at')],
            'condition'     => ['required', Rule::in(['GOOD', 'DAMAGED', 'UNDER_REPAIR', 'RETIRED'])],
            'purchased_at'  => ['nullable', 'date'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required'   => 'Rumah sakit wajib dipilih.',
            'instrument_id.required' => 'Instrumen wajib dipilih.',
            'code.required'          => 'Kode item wajib diisi.',
            'code.unique'            => 'Kode item sudah digunakan.',
            'serial_number.unique'   => 'Serial number sudah digunakan.',
            'barcode.unique'         => 'Barcode sudah digunakan.',
            'rfid_tag.unique'        => 'RFID tag sudah digunakan.',
            'condition.required'     => 'Kondisi wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}