<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrayTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['hospital_id' => 1, 'name' => 'Set Minor OK',          'code' => 'TPL-MIN-OK',  'description' => 'Set instrumen untuk operasi minor',            'is_lockable' => false],
            ['hospital_id' => 1, 'name' => 'Set Mayor Bedah Umum',  'code' => 'TPL-MAY-BDH', 'description' => 'Set instrumen operasi mayor bedah umum',       'is_lockable' => true],
            ['hospital_id' => 1, 'name' => 'Set Laparoskopi Dasar', 'code' => 'TPL-LAP-DSR', 'description' => 'Set instrumen laparoskopi prosedur dasar',     'is_lockable' => true],
            ['hospital_id' => 1, 'name' => 'Set Perawatan Luka',    'code' => 'TPL-PRW-LKA', 'description' => 'Set untuk ganti verban dan perawatan luka',    'is_lockable' => false],
        ];

        foreach ($templates as &$tpl) {
            $tpl['is_active']  = true;
            $tpl['created_by'] = 1;
            $tpl['updated_by'] = 1;
            $tpl['created_at'] = now();
            $tpl['updated_at'] = now();
        }

        DB::table('tray_templates')->insert($templates);

        // Isi template items untuk Set Minor OK (template_id: 1)
        $templateItems = [
            ['template_id' => 1, 'instrument_id' => 1, 'quantity' => 2], // Gunting Jaringan x2
            ['template_id' => 1, 'instrument_id' => 2, 'quantity' => 4], // Klem Mosquito x4
            ['template_id' => 1, 'instrument_id' => 3, 'quantity' => 2], // Pinset Anatomis x2
            ['template_id' => 1, 'instrument_id' => 4, 'quantity' => 1], // Needle Holder x1
            ['template_id' => 1, 'instrument_id' => 5, 'quantity' => 2], // Retractor x2
        ];

        foreach ($templateItems as &$item) {
            $item['is_active']  = true;
            $item['created_by'] = 1;
            $item['updated_by'] = 1;
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        DB::table('tray_template_items')->insert($templateItems);
    }
}