<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'Admin',
            'Operator CSSD',
            'Supervisor CSSD',
            'Kepala CSSD',
            'Teknisi',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name'       => $role,
                'guard_name' => 'web',
            ]);
        }

        // Assign role Admin ke user pertama
        $admin = \App\Models\User::where('username', 'superadmin')->first();
        if ($admin) {
            $admin->assignRole('Admin');
        }

        $admin = \App\Models\User::where('username', 'admin.tosyo')->first();
        if ($admin) {
            $admin->assignRole('Admin');
        }

        $kepala = \App\Models\User::where('username', 'dewi.kepala')->first();
        if ($kepala) {
            $kepala->assignRole('Kepala CSSD');
        }

        $supervisor = \App\Models\User::where('username', 'budi.santoso')->first();
        if ($supervisor) {
            $supervisor->assignRole('Supervisor CSSD');
        }

        $operator = \App\Models\User::where('username', 'siti.rahayu')->first();
        if ($operator) {
            $operator->assignRole('Operator CSSD');
        }

        $teknisi = \App\Models\User::where('username', 'ahmad.teknisi')->first();
        if ($teknisi) {
            $teknisi->assignRole('Teknisi');
        }
    }
}