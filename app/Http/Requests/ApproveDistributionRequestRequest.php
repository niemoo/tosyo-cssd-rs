<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveDistributionRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'decision'        => ['required', Rule::in(['approve', 'reject'])],
            'rejection_notes' => ['required_if:decision,reject', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required'         => 'Keputusan wajib dipilih.',
            'rejection_notes.required_if' => 'Alasan penolakan wajib diisi.',
        ];
    }
}