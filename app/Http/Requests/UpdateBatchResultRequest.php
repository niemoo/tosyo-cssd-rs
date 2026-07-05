<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchResultRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'results'                  => ['required', 'array'],
            'results.*.tray_id'        => ['required', 'exists:trays,id'],
            'results.*.result'         => ['required', Rule::in(['PASSED', 'FAILED'])],
            'results.*.failure_notes'  => ['nullable', 'string', 'max:500'],
            'rack_id'                  => ['nullable', 'exists:storage_racks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'results.required'             => 'Hasil sterilisasi wajib diisi.',
            'results.*.result.required'    => 'Hasil untuk setiap tray wajib dipilih.',
            'results.*.failure_notes.max'  => 'Catatan kegagalan maksimal 500 karakter.',
        ];
    }
}