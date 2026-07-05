<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageRackSeeder extends Seeder
{
    public function run(): void
    {
        $racks = [
            ['hospital_id' => 1, 'name' => 'Rak Steril A',  'code' => 'RACK-A',   'location_desc' => 'Ruang steril lantai 2, sisi kiri',     'capacity' => 30],
            ['hospital_id' => 1, 'name' => 'Rak Steril B',  'code' => 'RACK-B',   'location_desc' => 'Ruang steril lantai 2, sisi kanan',     'capacity' => 30],
            ['hospital_id' => 1, 'name' => 'Rak Steril C',  'code' => 'RACK-C',   'location_desc' => 'Ruang steril lantai 2, tengah',         'capacity' => 20],
            ['hospital_id' => 1, 'name' => 'Rak Karantina', 'code' => 'RACK-KRN', 'location_desc' => 'Ruang isolasi, akses terbatas',         'capacity' => 10],
            ['hospital_id' => 2, 'name' => 'Rak Steril A',  'code' => 'RACK-A',   'location_desc' => 'Ruang steril lantai 1',                 'capacity' => 25],
        ];

        foreach ($racks as &$rack) {
            $rack['is_active']  = true;
            $rack['created_by'] = 1;
            $rack['updated_by'] = 1;
            $rack['created_at'] = now();
            $rack['updated_at'] = now();
        }

        DB::table('storage_racks')->insert($racks);
    }
}