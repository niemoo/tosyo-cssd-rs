<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FulfillDistributionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Bentuk payload: assignments[<item_id>][] = <tray_id>.
        // Validasi rinci (status steril, kedaluwarsa, cocok template, kuota) dilakukan
        // di controller agar pesannya ramah & kontekstual.
        return [
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['nullable', 'array'],
        ];
    }
}
