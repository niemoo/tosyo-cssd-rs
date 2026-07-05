<?php

namespace Database\Seeders;

use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Sterilizer;
use App\Models\Tray;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class SterilizationBatchSeeder extends Seeder
{
    public function run(): void
    {
        $hospital    = Hospital::first();
        $user        = User::first();
        $sterilizers = Sterilizer::where('hospital_id', $hospital->id)
                                 ->where('is_active', true)
                                 ->get();
        $trays       = Tray::where('hospital_id', $hospital->id)->get();

        if (!$hospital || !$user || $sterilizers->isEmpty()) return;

        $batches = [
            [
                'batch_number'     => 'BATCH-2026-001',
                'status'           => SterilizationBatch::STATUS_COMPLETED,
                'temperature'      => 134.00,
                'pressure'         => 2.10,
                'duration_minutes' => 18,
                'started_at'       => now()->subDays(5)->setTime(8, 0),
                'completed_at'     => now()->subDays(5)->setTime(8, 30),
                'notes'            => 'Batch rutin pagi',
                'tray_results'     => [
                    ['tray_index' => 0, 'result' => SterilizationBatchItem::RESULT_PASSED],
                    ['tray_index' => 1, 'result' => SterilizationBatchItem::RESULT_PASSED],
                ],
            ],
            [
                'batch_number'     => 'BATCH-2026-002',
                'status'           => SterilizationBatch::STATUS_COMPLETED,
                'temperature'      => 134.00,
                'pressure'         => 2.10,
                'duration_minutes' => 18,
                'started_at'       => now()->subDays(3)->setTime(9, 0),
                'completed_at'     => now()->subDays(3)->setTime(9, 30),
                'notes'            => null,
                'tray_results'     => [
                    ['tray_index' => 2, 'result' => SterilizationBatchItem::RESULT_FAILED, 'failure_notes' => 'Indikator tidak berubah warna'],
                ],
            ],
            [
                'batch_number'     => 'BATCH-2026-003',
                'status'           => SterilizationBatch::STATUS_IN_PROGRESS,
                'temperature'      => 121.00,
                'pressure'         => 1.05,
                'duration_minutes' => 30,
                'started_at'       => now()->subHours(1),
                'completed_at'     => null,
                'notes'            => 'Batch siang',
                'tray_results'     => [],
            ],
            [
                'batch_number'     => 'BATCH-2026-004',
                'status'           => SterilizationBatch::STATUS_PENDING,
                'temperature'      => null,
                'pressure'         => null,
                'duration_minutes' => null,
                'started_at'       => null,
                'completed_at'     => null,
                'notes'            => 'Antrian batch sore',
                'tray_results'     => [],
            ],
        ];

        foreach ($batches as $data) {
            $batch = SterilizationBatch::create([
                'hospital_id'      => $hospital->id,
                'sterilizer_id'    => $sterilizers->first()->id,
                'batch_number'     => $data['batch_number'],
                'status'           => $data['status'],
                'temperature'      => $data['temperature'],
                'pressure'         => $data['pressure'],
                'duration_minutes' => $data['duration_minutes'],
                'operator_id'      => $user->id,
                'started_at'       => $data['started_at'],
                'completed_at'     => $data['completed_at'],
                'notes'            => $data['notes'],
                'created_by'       => $user->id,
                'updated_by'       => $user->id,
            ]);

            foreach ($data['tray_results'] as $item) {
                $tray = $trays->get($item['tray_index']);
                if (!$tray) continue;

                SterilizationBatchItem::create([
                    'batch_id'      => $batch->id,
                    'tray_id'       => $tray->id,
                    'result'        => $item['result'],
                    'failure_notes' => $item['failure_notes'] ?? null,
                ]);
            }
        }
    }
}