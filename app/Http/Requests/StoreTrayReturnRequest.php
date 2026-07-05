<?php

namespace App\Http\Requests;

use App\Models\TrayReturn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrayReturnRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'returns'                   => ['required', 'array', 'min:1'],
            'returns.*.tray_id'         => ['required', 'exists:trays,id'],
            'returns.*.condition'       => ['required', Rule::in([
                                                TrayReturn::CONDITION_GOOD,
                                                TrayReturn::CONDITION_DAMAGED,
                                                TrayReturn::CONDITION_INCOMPLETE,
                                            ])],
            'returns.*.missing_items'   => ['nullable', 'string', 'max:500', 'required_if:returns.*.condition,INCOMPLETE'],
            'returns.*.notes'           => ['nullable', 'string', 'max:500'],
            'returns.*.returned_at'     => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'returns.required'                  => 'Pilih minimal 1 tray untuk dicatat pengembaliannya.',
            'returns.*.condition.required'      => 'Kondisi tray wajib dipilih.',
            'returns.*.missing_items.required_if' => 'Sebutkan instrumen yang hilang/tidak lengkap.',
            'returns.*.returned_at.required'    => 'Tanggal pengembalian wajib diisi.',
        ];
    }
}