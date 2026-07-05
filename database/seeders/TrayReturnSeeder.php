<?php

namespace Database\Seeders;

use App\Models\TrayReturn;
use App\Models\DistributionRequest;
use App\Models\Tray;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrayReturnSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::first();
        $user     = User::first();

        $fulfilledRequest = DistributionRequest::where('hospital_id', $hospital->id)
                                               ->where('status', DistributionRequest::STATUS_FULFILLED)
                                               ->first();

        $tray = Tray::where('hospital_id', $hospital->id)
                    ->where('status', Tray::STATUS_RETURNED)
                    ->first();

        if (!$hospital || !$user || !$fulfilledRequest || !$tray) return;

        TrayReturn::create([
            'hospital_id'              => $hospital->id,
            'distribution_request_id'  => $fulfilledRequest->id,
            'tray_id'                  => $tray->id,
            'received_by'              => $user->id,
            'condition'                => TrayReturn::CONDITION_GOOD,
            'missing_items'            => null,
            'notes'                    => 'Tray dikembalikan dalam kondisi baik',
            'returned_at'              => now()->subDay(),
            'created_by'               => $user->id,
            'updated_by'               => $user->id,
        ]);

        // Contoh pengembalian dengan kondisi tidak lengkap
        $trayIncomplete = Tray::where('hospital_id', $hospital->id)
                              ->where('status', Tray::STATUS_NEEDS_REPROCESSING)
                              ->first();

        if ($trayIncomplete) {
            TrayReturn::create([
                'hospital_id'              => $hospital->id,
                'distribution_request_id'  => $fulfilledRequest->id,
                'tray_id'                  => $trayIncomplete->id,
                'received_by'              => $user->id,
                'condition'                => TrayReturn::CONDITION_INCOMPLETE,
                'missing_items'            => 'Gunting Bedah Mayo (1 unit), Klem Mosquito (2 unit)',
                'notes'                    => 'Beberapa instrumen tidak dikembalikan',
                'returned_at'              => now()->subHours(6),
                'created_by'               => $user->id,
                'updated_by'               => $user->id,
            ]);
        }
    }
}