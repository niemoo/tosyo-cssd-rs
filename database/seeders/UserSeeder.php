<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username'   => 'superadmin',
                'password'   => bcrypt('password'),
                'name'       => 'Super Administrator',
                'phone'      => '08111000000',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'admin.tosyo',
                'password'   => bcrypt('password'),
                'name'       => 'Administrator Tosyo',
                'phone'      => '08111000001',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'budi.santoso',
                'password'   => bcrypt('password'),
                'name'       => 'Dr. Budi Santoso',
                'phone'      => '08111000002',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'siti.rahayu',
                'password'   => bcrypt('password'),
                'name'       => 'Siti Rahayu, Amd.CSSD',
                'phone'      => '08111000003',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'ahmad.teknisi',
                'password'   => bcrypt('password'),
                'name'       => 'Ahmad Fauzi',
                'phone'      => '08111000004',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'dewi.kepala',
                'password'   => bcrypt('password'),
                'name'       => 'Dewi Kusuma, S.Kep',
                'phone'      => '08111000005',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}