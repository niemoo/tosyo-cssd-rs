<?php

namespace App\Http\Requests;

use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bi_result' => ['required', Rule::in([
                SterilizationBatch::BI_PENDING,
                SterilizationBatch::BI_NEGATIVE,
                SterilizationBatch::BI_POSITIVE,
            ])],
            'results' => ['required', 'array'],
            'results.*.tray_id' => ['required', 'exists:trays,id'],
            'results.*.result' => ['required', Rule::in([
                SterilizationBatchItem::RESULT_PASSED,
                SterilizationBatchItem::RESULT_FAILED,
            ])],
            'results.*.ci_result' => ['nullable', Rule::in([
                SterilizationBatchItem::CI_PASS,
                SterilizationBatchItem::CI_FAIL,
            ])],
            'results.*.failure_notes' => ['nullable', 'string', 'max:500'],
            'rack_id' => ['nullable', 'exists:storage_racks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'bi_result.required' => 'Hasil indikator biologis (BI) wajib dipilih.',
            'results.required' => 'Hasil sterilisasi wajib diisi.',
            'results.*.result.required' => 'Hasil untuk setiap tray wajib dipilih.',
            'results.*.failure_notes.max' => 'Catatan kegagalan maksimal 500 karakter.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $biPositive = $this->input('bi_result') === SterilizationBatch::BI_POSITIVE;

            foreach ($this->input('results', []) as $i => $row) {
                if (($row['result'] ?? null) !== SterilizationBatchItem::RESULT_PASSED) {
                    continue;
                }

                // BI positif → tidak boleh ada tray yang diluluskan.
                if ($biPositive) {
                    $validator->errors()->add(
                        "results.$i.result",
                        'BI Positif: seluruh tray harus Gagal, tidak boleh diluluskan.'
                    );
                }

                // Meluluskan tray wajib CI Lulus.
                if (($row['ci_result'] ?? null) !== SterilizationBatchItem::CI_PASS) {
                    $validator->errors()->add(
                        "results.$i.ci_result",
                        'Indikator Kimia (CI) harus Lulus untuk meluluskan tray ini.'
                    );
                }
            }
        });
    }
}
