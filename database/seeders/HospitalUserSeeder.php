<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalUserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // admin.tosyo di RS Pusat
            [
                'hospital_id' => 1, 'user_id' => 1,
                'joined_at'   => '2024-01-01', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
            // Dr. Budi di RS Pusat
            [
                'hospital_id' => 1, 'user_id' => 2,
                'joined_at'   => '2024-03-01', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
            // Dr. Budi juga di RS Bekasi (multi-hospital)
            [
                'hospital_id' => 2, 'user_id' => 2,
                'joined_at'   => '2024-06-01', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
            // Siti di RS Pusat
            [
                'hospital_id' => 1, 'user_id' => 3,
                'joined_at'   => '2024-01-15', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
            // Ahmad di RS Pusat
            [
                'hospital_id' => 1, 'user_id' => 4,
                'joined_at'   => '2024-02-01', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
            // Dewi di RS Pusat
            [
                'hospital_id' => 1, 'user_id' => 5,
                'joined_at'   => '2024-01-10', 'is_active' => true,
                'created_by'  => 1, 'updated_by' => 1,
                'created_at'  => now(), 'updated_at' => now(),
            ],
        ];

        DB::table('hospital_users')->insert($data);
    }
}