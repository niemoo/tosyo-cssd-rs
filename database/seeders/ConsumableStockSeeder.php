<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableStockSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            ['hospital_id' => 1, 'consumable_id' => 1, 'quantity' => 1200, 'last_updated_at' => now()],
            ['hospital_id' => 1, 'consumable_id' => 2, 'quantity' => 450,  'last_updated_at' => now()],
            ['hospital_id' => 1, 'consumable_id' => 3, 'quantity' => 180,  'last_updated_at' => now()],
            ['hospital_id' => 1, 'consumable_id' => 4, 'quantity' => 320,  'last_updated_at' => now()],
            ['hospital_id' => 1, 'consumable_id' => 5, 'quantity' => 95,   'last_updated_at' => now()],
            ['hospital_id' => 1, 'consumable_id' => 6, 'quantity' => 40,   'last_updated_at' => now()], // di bawah minimum (50)!
            ['hospital_id' => 1, 'consumable_id' => 7, 'quantity' => 8,    'last_updated_at' => now()], // di bawah minimum (10)!
            ['hospital_id' => 1, 'consumable_id' => 8, 'quantity' => 3,    'last_updated_at' => now()], // di bawah minimum (5)!
        ];

        foreach ($stocks as &$stock) {
            $stock['created_by'] = 1;
            $stock['updated_by'] = 1;
            $stock['created_at'] = now();
            $stock['updated_at'] = now();
        }

        DB::table('consumable_stocks')->insert($stocks);
    }
}