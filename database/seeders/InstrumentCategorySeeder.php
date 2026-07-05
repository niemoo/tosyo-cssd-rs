<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['hospital_id' => 1, 'name' => 'Instrumen Bedah Umum',  'code' => 'CAT-BDH'],
            ['hospital_id' => 1, 'name' => 'Instrumen Orthopedi',   'code' => 'CAT-ORT'],
            ['hospital_id' => 1, 'name' => 'Instrumen Laparoskopi', 'code' => 'CAT-LAP'],
            ['hospital_id' => 1, 'name' => 'Instrumen Kebidanan',   'code' => 'CAT-KBD'],
            ['hospital_id' => 1, 'name' => 'Instrumen Endoskopi',   'code' => 'CAT-END'],
        ];

        foreach ($categories as &$cat) {
            $cat['is_active']  = true;
            $cat['created_by'] = 1;
            $cat['updated_by'] = 1;
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
        }

        DB::table('instrument_categories')->insert($categories);
    }
}