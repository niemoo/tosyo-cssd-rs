<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // RS Pusat (hospital_id: 1)
            ['hospital_id' => 1, 'name' => 'Instalasi Bedah Sentral', 'code' => 'IBS-01', 'type' => 'OK'],
            ['hospital_id' => 1, 'name' => 'Instalasi Gawat Darurat', 'code' => 'IGD-01', 'type' => 'IGD'],
            ['hospital_id' => 1, 'name' => 'Intensive Care Unit',     'code' => 'ICU-01', 'type' => 'ICU'],
            ['hospital_id' => 1, 'name' => 'Bangsal Bedah Umum',      'code' => 'BBU-01', 'type' => 'Bangsal'],
            ['hospital_id' => 1, 'name' => 'Poliklinik Bedah',        'code' => 'PLK-01', 'type' => 'Poliklinik'],
            // RS Bekasi (hospital_id: 2)
            ['hospital_id' => 2, 'name' => 'Instalasi Bedah Sentral', 'code' => 'IBS-01', 'type' => 'OK'],
            ['hospital_id' => 2, 'name' => 'Instalasi Gawat Darurat', 'code' => 'IGD-01', 'type' => 'IGD'],
        ];

        foreach ($units as &$unit) {
            $unit['is_active']  = true;
            $unit['created_by'] = 1;
            $unit['updated_by'] = 1;
            $unit['created_at'] = now();
            $unit['updated_at'] = now();
        }

        DB::table('units')->insert($units);
    }
}