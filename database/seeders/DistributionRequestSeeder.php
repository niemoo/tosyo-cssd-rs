<?php

namespace Database\Seeders;

use App\Models\DistributionRequest;
use App\Models\DistributionRequestItem;
use App\Models\TrayTemplate;
use App\Models\Tray;
use App\Models\Unit;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $hospital  = Hospital::first();
        $user      = User::first();
        $units     = Unit::where('hospital_id', $hospital->id)->get();
        $templates = TrayTemplate::where('hospital_id', $hospital->id)->get();
        $trays     = Tray::where('hospital_id', $hospital->id)
                         ->where('status', Tray::STATUS_STERILE)
                         ->get();

        if (!$hospital || !$user || $units->isEmpty()) return;

        $requests = [
            [
                'request_number' => 'REQ-2026-001',
                'status'         => DistributionRequest::STATUS_FULFILLED,
                'unit_index'     => 0,
                'notes'          => 'Untuk operasi elektif besok pagi',
                'approved_at'    => now()->subDays(3),
                'fulfilled_at'   => now()->subDays(2),
                'items'          => [
                    ['template_index' => 0, 'quantity' => 2, 'tray_index' => 0],
                ],
            ],
            [
                'request_number' => 'REQ-2026-002',
                'status'         => DistributionRequest::STATUS_APPROVED,
                'unit_index'     => 1,
                'notes'          => 'Kebutuhan rutin bangsal',
                'approved_at'    => now()->subDay(),
                'fulfilled_at'   => null,
                'items'          => [
                    ['template_index' => 1, 'quantity' => 1, 'tray_index' => null],
                    ['template_index' => 2, 'quantity' => 2, 'tray_index' => null],
                ],
            ],
            [
                'request_number' => 'REQ-2026-003',
                'status'         => DistributionRequest::STATUS_PENDING_APPROVAL,
                'unit_index'     => 0,
                'notes'          => null,
                'approved_at'    => null,
                'fulfilled_at'   => null,
                'items'          => [
                    ['template_index' => 0, 'quantity' => 1, 'tray_index' => null],
                ],
            ],
            [
                'request_number' => 'REQ-2026-004',
                'status'         => DistributionRequest::STATUS_REJECTED,
                'unit_index'     => 2,
                'notes'          => 'Urgent perlu segera',
                'approved_at'    => null,
                'fulfilled_at'   => null,
                'rejection_notes'=> 'Stok tray steril sedang habis, harap tunggu batch berikutnya',
                'items'          => [
                    ['template_index' => 0, 'quantity' => 3, 'tray_index' => null],
                ],
            ],
            [
                'request_number' => 'REQ-2026-005',
                'status'         => DistributionRequest::STATUS_DRAFT,
                'unit_index'     => 1,
                'notes'          => 'Draft permintaan mingguan',
                'approved_at'    => null,
                'fulfilled_at'   => null,
                'items'          => [
                    ['template_index' => 1, 'quantity' => 2, 'tray_index' => null],
                ],
            ],
        ];

        foreach ($requests as $data) {
            $unit = $units->get($data['unit_index']) ?? $units->first();

            $request = DistributionRequest::create([
                'hospital_id'     => $hospital->id,
                'unit_id'         => $unit->id,
                'request_number'  => $data['request_number'],
                'status'          => $data['status'],
                'requested_by'    => $user->id,
                'approved_by'     => $data['approved_at'] ? $user->id : null,
                'fulfilled_by'    => $data['fulfilled_at'] ? $user->id : null,
                'requested_at'    => now()->subDays(5),
                'approved_at'     => $data['approved_at'],
                'fulfilled_at'    => $data['fulfilled_at'],
                'notes'           => $data['notes'],
                'rejection_notes' => $data['rejection_notes'] ?? null,
                'revision_notes'  => null,
                'created_by'      => $user->id,
                'updated_by'      => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $template = $templates->get($item['template_index']);
                $tray     = isset($item['tray_index']) && $item['tray_index'] !== null
                            ? $trays->get($item['tray_index'])
                            : null;

                DistributionRequestItem::create([
                    'request_id'  => $request->id,
                    'template_id' => $template?->id,
                    'tray_id'     => $tray?->id,
                    'quantity'    => $item['quantity'],
                    'notes'       => null,
                ]);
            }
        }
    }
}