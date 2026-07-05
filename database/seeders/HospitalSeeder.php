<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = [
            [
                'name'       => 'RS Sehat Jaya Pusat',
                'code'       => 'RSJ-001',
                'address'    => 'Jl. Sudirman No. 10, Jakarta Pusat',
                'phone'      => '021-5551001',
                'is_active'  => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'RS Sehat Jaya Cabang Bekasi',
                'code'       => 'RSJ-002',
                'address'    => 'Jl. Ahmad Yani No. 45, Bekasi',
                'phone'      => '021-5552002',
                'is_active'  => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'RS Maju Makmur',
                'code'       => 'RSM-001',
                'address'    => 'Jl. Gatot Subroto No. 88, Bandung',
                'phone'      => '022-5553003',
                'is_active'  => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hospitals')->insert($hospitals);
    }
}