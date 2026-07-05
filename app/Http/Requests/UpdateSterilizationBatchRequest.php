<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSterilizationBatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'temperature'      => ['nullable', 'numeric', 'min:0', 'max:999'],
            'pressure'         => ['nullable', 'numeric', 'min:0', 'max:999'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'started_at'       => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}