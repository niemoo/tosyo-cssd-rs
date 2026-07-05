<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instruments = [
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Gunting Jaringan Lurus', 'code' => 'INS-BDH-001', 'brand' => 'Aesculap', 'material' => 'Stainless 316L', 'lifespan_cycles' => 500],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Klem Mosquito Bengkok',  'code' => 'INS-BDH-002', 'brand' => 'Aesculap', 'material' => 'Stainless 316L', 'lifespan_cycles' => 500],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Pinset Anatomis 18cm',   'code' => 'INS-BDH-003', 'brand' => 'Mopec',    'material' => 'Stainless 304',  'lifespan_cycles' => 300],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Needle Holder Mayo',     'code' => 'INS-BDH-004', 'brand' => 'Aesculap', 'material' => 'Stainless 316L', 'lifespan_cycles' => 400],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Retractor Farabeuf',     'code' => 'INS-BDH-005', 'brand' => 'KLS Martin','material' => 'Stainless 316L', 'lifespan_cycles' => 600],
            ['hospital_id' => 1, 'category_id' => 2, 'name' => 'Osteotome 10mm',         'code' => 'INS-ORT-001', 'brand' => 'Synthes',   'material' => 'Stainless 316L', 'lifespan_cycles' => 200],
            ['hospital_id' => 1, 'category_id' => 3, 'name' => 'Trokar 5mm',             'code' => 'INS-LAP-001', 'brand' => 'Karl Storz','material' => 'Titanium',       'lifespan_cycles' => 150],
        ];

        foreach ($instruments as &$ins) {
            $ins['is_active']  = true;
            $ins['created_by'] = 1;
            $ins['updated_by'] = 1;
            $ins['created_at'] = now();
            $ins['updated_at'] = now();
        }

        DB::table('instruments')->insert($instruments);
    }
}