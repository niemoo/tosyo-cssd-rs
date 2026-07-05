<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ConsumableStock;
use App\Models\Consumable;

class StoreSterilizationBatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hospital_id'      => ['required', 'exists:hospitals,id'],
            'sterilizer_id'    => ['required', 'exists:sterilizers,id'],
            'batch_number'     => ['required', 'string', 'max:50',
                                   Rule::unique('sterilization_batches')
                                       ->where('hospital_id', $this->hospital_id)],
            'temperature'      => ['nullable', 'numeric', 'min:0', 'max:999'],
            'pressure'         => ['nullable', 'numeric', 'min:0', 'max:999'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'started_at'       => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'tray_ids'         => ['required', 'array', 'min:1'],
            'tray_ids.*'       => ['required', 'exists:trays,id'],

            'consumable_usages'                  => ['nullable', 'array'],
            'consumable_usages.*.consumable_id'  => ['required', 'exists:consumables,id'],
            'consumable_usages.*.quantity'       => ['required', 'integer', 'min:1'],
            'consumable_usages.*.notes'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required'   => 'Rumah sakit wajib dipilih.',
            'sterilizer_id.required' => 'Sterilizer wajib dipilih.',
            'batch_number.required'  => 'Nomor batch wajib diisi.',
            'batch_number.unique'    => 'Nomor batch sudah digunakan.',
            'tray_ids.required'      => 'Pilih minimal 1 tray.',
            'tray_ids.min'           => 'Pilih minimal 1 tray.',
            'consumable_usages.*.consumable_id.required' => 'Pilih consumable atau hapus baris ini.',
            'consumable_usages.*.quantity.required'      => 'Jumlah pemakaian wajib diisi.',
            'consumable_usages.*.quantity.min'            => 'Jumlah minimal 1.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hospitalId = $this->input('hospital_id');

            foreach ($this->input('consumable_usages', []) as $i => $row) {
                if (empty($row['consumable_id']) || empty($row['quantity'])) continue;

                $stock = ConsumableStock::where('hospital_id', $hospitalId)
                                        ->where('consumable_id', $row['consumable_id'])
                                        ->first();

                $available = $stock?->quantity ?? 0;

                if ((int) $row['quantity'] > $available) {
                    $consumable = Consumable::find($row['consumable_id']);
                    $validator->errors()->add(
                        "consumable_usages.$i.quantity",
                        "Stok {$consumable?->name} tidak cukup (tersedia: {$available} {$consumable?->unit})."
                    );
                }
            }
        });
    }
}