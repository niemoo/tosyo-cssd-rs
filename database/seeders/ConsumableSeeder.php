<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableSeeder extends Seeder
{
    public function run(): void
    {
        $consumables = [
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Pouches Steril 100x200mm',      'code' => 'CSM-PKG-001', 'unit' => 'PCS',   'minimum_stock' => 500],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Pouches Steril 150x250mm',      'code' => 'CSM-PKG-002', 'unit' => 'PCS',   'minimum_stock' => 300],
            ['hospital_id' => 1, 'category_id' => 1, 'name' => 'Wrapping Paper 60x60cm',        'code' => 'CSM-PKG-003', 'unit' => 'PCS',   'minimum_stock' => 200],
            ['hospital_id' => 1, 'category_id' => 2, 'name' => 'Chemical Indicator Class 4',    'code' => 'CSM-CHM-001', 'unit' => 'PCS',   'minimum_stock' => 100],
            ['hospital_id' => 1, 'category_id' => 2, 'name' => 'Chemical Indicator Class 5',    'code' => 'CSM-CHM-002', 'unit' => 'PCS',   'minimum_stock' => 100],
            ['hospital_id' => 1, 'category_id' => 3, 'name' => 'Biological Indicator Spore',    'code' => 'CSM-BIO-001', 'unit' => 'PCS',   'minimum_stock' => 50],
            ['hospital_id' => 1, 'category_id' => 4, 'name' => 'Enzymatic Detergent 5L',        'code' => 'CSM-CLN-001', 'unit' => 'LITER', 'minimum_stock' => 10],
            ['hospital_id' => 1, 'category_id' => 4, 'name' => 'Disinfektan Permukaan 1L',      'code' => 'CSM-CLN-002', 'unit' => 'LITER', 'minimum_stock' => 5],
        ];

        foreach ($consumables as &$csm) {
            $csm['is_active']  = true;
            $csm['created_by'] = 1;
            $csm['updated_by'] = 1;
            $csm['created_at'] = now();
            $csm['updated_at'] = now();
        }

        DB::table('consumables')->insert($consumables);
    }
}