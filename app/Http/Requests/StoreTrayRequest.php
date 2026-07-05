<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ConsumableStock;
use App\Models\Consumable;

class StoreTrayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hospital_id'  => ['required', 'exists:hospitals,id'],
            'template_id'  => ['nullable', 'exists:tray_templates,id'],
            'code'         => ['required', 'string', 'max:50', 'unique:trays,code'],
            'name'         => ['required', 'string', 'max:255'],
            'barcode'      => ['nullable', 'string', 'max:100', 'unique:trays,barcode'],
            'notes'        => ['nullable', 'string', 'max:500'],
            'is_active'    => ['boolean'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.instrument_item_id' => ['required', 'exists:instrument_items,id'],
            'items.*.notes'             => ['nullable', 'string', 'max:255'],

            'consumable_usages'                  => ['nullable', 'array'],
            'consumable_usages.*.consumable_id'  => ['required', 'exists:consumables,id'],
            'consumable_usages.*.quantity'       => ['required', 'integer', 'min:1'],
            'consumable_usages.*.notes'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'hospital_id.required'              => 'Rumah sakit wajib dipilih.',
            'code.required'                     => 'Kode tray wajib diisi.',
            'code.unique'                       => 'Kode tray sudah digunakan.',
            'name.required'                     => 'Nama tray wajib diisi.',
            'barcode.unique'                    => 'Barcode sudah digunakan.',
            'items.required'                    => 'Tray harus memiliki minimal 1 instrumen.',
            'items.min'                         => 'Tray harus memiliki minimal 1 instrumen.',
            'items.*.instrument_item_id.required' => 'Item instrumen wajib dipilih.',
            'items.*.instrument_item_id.exists'   => 'Item instrumen tidak valid.',
            'consumable_usages.*.consumable_id.required' => 'Pilih consumable atau hapus baris ini.',
            'consumable_usages.*.quantity.required'      => 'Jumlah pemakaian wajib diisi.',
            'consumable_usages.*.quantity.min'            => 'Jumlah minimal 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
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