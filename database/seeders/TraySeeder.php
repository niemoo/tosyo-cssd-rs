<?php

namespace Database\Seeders;

use App\Models\Tray;
use App\Models\TrayItem;
use App\Models\TrayTemplate;
use App\Models\InstrumentItem;
use App\Models\StorageRack;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class TraySeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::first();
        $user     = User::first();

        if (!$hospital || !$user) return;

        // Hapus data lama dulu
        TrayItem::query()->delete();
        Tray::withTrashed()->forceDelete();

        $templates   = TrayTemplate::where('hospital_id', $hospital->id)->get();
        $racks       = StorageRack::where('hospital_id', $hospital->id)->get();
        $instruments = InstrumentItem::where('hospital_id', $hospital->id)
                                    ->where('is_active', true)
                                    ->get();

        $trays = [
            [
                'code'        => 'TRY-001',
                'name'        => 'Set Bedah Minor #1',
                'status'      => Tray::STATUS_STERILE,
                'template_id' => $templates->first()?->id,
                'rack_id'     => $racks->first()?->id,
            ],
            [
                'code'        => 'TRY-002',
                'name'        => 'Set Bedah Minor #2',
                'status'      => Tray::STATUS_READY,
                'template_id' => $templates->first()?->id,
                'rack_id'     => null,
            ],
            [
                'code'        => 'TRY-003',
                'name'        => 'Set Rawat Luka #1',
                'status'      => Tray::STATUS_ASSEMBLING,
                'template_id' => $templates->skip(1)->first()?->id,
                'rack_id'     => null,
            ],
            [
                'code'        => 'TRY-004',
                'name'        => 'Set Persalinan #1',
                'status'      => Tray::STATUS_IN_USE,
                'template_id' => $templates->skip(2)->first()?->id,
                'rack_id'     => null,
            ],
            [
                'code'        => 'TRY-005',
                'name'        => 'Tray Bebas #1',
                'status'      => Tray::STATUS_NEEDS_REPROCESSING,
                'template_id' => null,
                'rack_id'     => null,
            ],
        ];

        foreach ($trays as $data) {
            $tray = Tray::create([
                'hospital_id'     => $hospital->id,
                'template_id'     => $data['template_id'],
                'code'            => $data['code'],
                'name'            => $data['name'],
                'status'          => $data['status'],
                'current_rack_id' => $data['rack_id'],
                'assembled_by'    => $user->id,
                'assembled_at'    => now()->subDays(rand(1, 10)),
                'is_active'       => true,
                'created_by'      => $user->id,
                'updated_by'      => $user->id,
            ]);

            if ($instruments->count() > 0) {
                $itemsToAttach = $instruments->random(min(3, $instruments->count()));
                foreach ($itemsToAttach as $item) {
                    TrayItem::firstOrCreate([
                        'tray_id'            => $tray->id,
                        'instrument_item_id' => $item->id,
                    ], [
                        'notes' => null,
                    ]);
                }
            }
        }
    }
}