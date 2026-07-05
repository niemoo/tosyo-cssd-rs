<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Gunting Jaringan Lurus (instrument_id: 1) — 5 unit fisik
            ['instrument_id' => 1, 'serial_number' => 'AES-2021-GJL-001', 'code' => 'ITM-BDH-001-01', 'barcode' => '8991299100001', 'condition' => 'GOOD',         'total_cycles' => 230, 'current_tray_id' => 1],
            ['instrument_id' => 1, 'serial_number' => 'AES-2021-GJL-002', 'code' => 'ITM-BDH-001-02', 'barcode' => '8991299100002', 'condition' => 'GOOD',         'total_cycles' => 187, 'current_tray_id' => 2],
            ['instrument_id' => 1, 'serial_number' => 'AES-2021-GJL-003', 'code' => 'ITM-BDH-001-03', 'barcode' => '8991299100003', 'condition' => 'UNDER_REPAIR', 'total_cycles' => 412, 'current_tray_id' => null],
            ['instrument_id' => 1, 'serial_number' => 'AES-2022-GJL-004', 'code' => 'ITM-BDH-001-04', 'barcode' => '8991299100004', 'condition' => 'GOOD',         'total_cycles' => 98,  'current_tray_id' => 3],
            ['instrument_id' => 1, 'serial_number' => 'AES-2022-GJL-005', 'code' => 'ITM-BDH-001-05', 'barcode' => '8991299100005', 'condition' => 'GOOD',         'total_cycles' => 45,  'current_tray_id' => null],
            // Klem Mosquito Bengkok (instrument_id: 2) — 3 unit fisik
            ['instrument_id' => 2, 'serial_number' => 'AES-2021-KMB-001', 'code' => 'ITM-BDH-002-01', 'barcode' => '8991299100006', 'condition' => 'GOOD',    'total_cycles' => 310, 'current_tray_id' => 1],
            ['instrument_id' => 2, 'serial_number' => 'AES-2021-KMB-002', 'code' => 'ITM-BDH-002-02', 'barcode' => '8991299100007', 'condition' => 'GOOD',    'total_cycles' => 275, 'current_tray_id' => 1],
            ['instrument_id' => 2, 'serial_number' => 'AES-2022-KMB-003', 'code' => 'ITM-BDH-002-03', 'barcode' => '8991299100008', 'condition' => 'DAMAGED', 'total_cycles' => 489, 'current_tray_id' => null],
        ];

        foreach ($items as &$item) {
            $item['hospital_id'] = 1;
            $item['rfid_tag']    = null;
            $item['purchased_at']= '2021-01-01';
            $item['is_active']   = true;
            $item['created_by']  = 1;
            $item['updated_by']  = 1;
            $item['created_at']  = now();
            $item['updated_at']  = now();
        }

        DB::table('instrument_items')->insert($items);
    }
}