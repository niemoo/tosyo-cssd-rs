<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SterilizerSeeder extends Seeder
{
    public function run(): void
    {
        $sterilizers = [
            ['hospital_id' => 1, 'name' => 'Autoclave Besar A', 'code' => 'STR-STM-001', 'type' => 'STEAM',  'capacity' => 20, 'serial_number' => 'AC-2021-00123', 'last_maintenance_at' => '2024-12-01', 'next_maintenance_at' => '2025-03-01'],
            ['hospital_id' => 1, 'name' => 'Autoclave Besar B', 'code' => 'STR-STM-002', 'type' => 'STEAM',  'capacity' => 20, 'serial_number' => 'AC-2021-00124', 'last_maintenance_at' => '2024-11-15', 'next_maintenance_at' => '2025-02-15'],
            ['hospital_id' => 1, 'name' => 'Plasma Sterilizer', 'code' => 'STR-PLS-001', 'type' => 'PLASMA', 'capacity' => 8,  'serial_number' => 'PS-2022-00056', 'last_maintenance_at' => '2024-12-10', 'next_maintenance_at' => '2025-03-10'],
            ['hospital_id' => 1, 'name' => 'EO Sterilizer',     'code' => 'STR-EO-001',  'type' => 'EO',     'capacity' => 5,  'serial_number' => 'EO-2020-00089', 'last_maintenance_at' => '2024-10-01', 'next_maintenance_at' => '2025-01-01'],
            ['hospital_id' => 2, 'name' => 'Autoclave Bekasi A','code' => 'STR-STM-001', 'type' => 'STEAM',  'capacity' => 15, 'serial_number' => 'AC-2022-00201', 'last_maintenance_at' => '2024-12-20', 'next_maintenance_at' => '2025-03-20'],
        ];

        foreach ($sterilizers as &$s) {
            $s['is_active']  = true;
            $s['created_by'] = 1;
            $s['updated_by'] = 1;
            $s['created_at'] = now();
            $s['updated_at'] = now();
        }

        DB::table('sterilizers')->insert($sterilizers);
    }
}