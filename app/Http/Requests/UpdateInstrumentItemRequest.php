<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstrumentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('instrument_item')?->id;

        return [
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'serial_number')->ignore($itemId)->whereNull('deleted_at')],
            'barcode'       => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'barcode')->ignore($itemId)->whereNull('deleted_at')],
            'rfid_tag'      => ['nullable', 'string', 'max:100', Rule::unique('instrument_items', 'rfid_tag')->ignore($itemId)->whereNull('deleted_at')],
            'condition'     => ['required', Rule::in(['GOOD', 'DAMAGED', 'UNDER_REPAIR', 'RETIRED'])],
            'purchased_at'  => ['nullable', 'date'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.unique' => 'Serial number sudah digunakan.',
            'barcode.unique'       => 'Barcode sudah digunakan.',
            'rfid_tag.unique'      => 'RFID tag sudah digunakan.',
            'condition.required'   => 'Kondisi wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}