<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['hospital_id' => 1, 'name' => 'Packaging & Wrapping', 'code' => 'CAT-PKG'],
            ['hospital_id' => 1, 'name' => 'Chemical Indicator',   'code' => 'CAT-CHM'],
            ['hospital_id' => 1, 'name' => 'Biological Indicator', 'code' => 'CAT-BIO'],
            ['hospital_id' => 1, 'name' => 'Cleaning Agent',       'code' => 'CAT-CLN'],
            ['hospital_id' => 1, 'name' => 'Spare Part Sterilizer','code' => 'CAT-SPR'],
        ];

        foreach ($categories as &$cat) {
            $cat['is_active']  = true;
            $cat['created_by'] = 1;
            $cat['updated_by'] = 1;
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
        }

        DB::table('consumable_categories')->insert($categories);
    }
}