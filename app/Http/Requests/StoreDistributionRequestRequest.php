<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hospital_id' => ['required', 'exists:hospitals,id'],
            'unit_id'     => ['required', 'exists:units,id'],
            'notes'       => ['nullable', 'string', 'max:500'],
            'submit_type' => ['required', 'in:draft,submit'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.template_id'   => ['nullable', 'exists:tray_templates,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.notes'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required'        => 'Rumah sakit wajib dipilih.',
            'unit_id.required'             => 'Unit wajib dipilih.',
            'items.required'               => 'Minimal 1 item permintaan diperlukan.',
            'items.min'                    => 'Minimal 1 item permintaan diperlukan.',
            'items.*.quantity.required'    => 'Jumlah wajib diisi.',
            'items.*.quantity.min'         => 'Jumlah minimal 1.',
        ];
    }
}