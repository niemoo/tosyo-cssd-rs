<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FulfillDistributionRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'assignments'              => ['nullable', 'array'],
            'assignments.*.item_id'    => ['required', 'exists:distribution_request_items,id'],
            'assignments.*.tray_id'    => ['required', 'exists:trays,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignments.*.item_id.required' => 'Item tidak valid.',
            'assignments.*.tray_id.required' => 'Setiap item yang ditampilkan wajib dipilih traynya.',
            'assignments.*.tray_id.exists'   => 'Tray yang dipilih tidak valid atau sudah tidak tersedia.',
        ];
    }
}