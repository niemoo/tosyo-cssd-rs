<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Hospitals
            'hospitals.view', 'hospitals.create',
            'hospitals.edit', 'hospitals.delete',

            // Users
            'users.view', 'users.create',
            'users.edit', 'users.delete',

            // Roles
            'roles.view', 'roles.create',
            'roles.edit', 'roles.delete',

            // Units
            'units.view', 'units.create',
            'units.edit', 'units.delete',

            // Instrument Categories
            'instrument-categories.view', 'instrument-categories.create',
            'instrument-categories.edit', 'instrument-categories.delete',

            // Instruments
            'instruments.view', 'instruments.create',
            'instruments.edit', 'instruments.delete',

            // Instrument Items
            'instrument-items.view', 'instrument-items.create',
            'instrument-items.edit', 'instrument-items.delete',

            // Tray Templates
            'tray-templates.view', 'tray-templates.create',
            'tray-templates.edit', 'tray-templates.delete',

            // Trays
            'trays.view', 'trays.create',
            'trays.edit', 'trays.delete',

            // Sterilizers
            'sterilizers.view', 'sterilizers.create',
            'sterilizers.edit', 'sterilizers.delete',

            // Storage Racks
            'storage-racks.view', 'storage-racks.create',
            'storage-racks.edit', 'storage-racks.delete',

            // Consumable Categories
            'consumable-categories.view', 'consumable-categories.create',
            'consumable-categories.edit', 'consumable-categories.delete',

            // Consumables
            'consumables.view', 'consumables.create',
            'consumables.edit', 'consumables.delete',

            // Consumable Stocks
            'consumable-stocks.view', 'consumable-stocks.edit',

            // Consumable Movements
            'consumable-movements.view', 'consumable-movements.create',

            // Distribution Requests
            'distribution-requests.view', 'distribution-requests.create',
            'distribution-requests.approve', 'distribution-requests.fulfill',
            'distribution-requests.return', 'distribution-requests.delete',

            // Trays
            'trays.view', 'trays.create', 'trays.edit',

            // Sterilization Batches
            'sterilization-batches.view', 'sterilization-batches.create',
            'sterilization-batches.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign semua permission ke Admin
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        // Kepala CSSD
        $kepala = Role::where('name', 'Kepala CSSD')->first();
        if ($kepala) {
            $kepala->syncPermissions([
                'dashboard.view',
                'units.view',
                'instruments.view', 'instrument-items.view',
                'tray-templates.view', 'trays.view',
                'sterilizers.view', 'storage-racks.view',
                'consumables.view', 'consumable-stocks.view',
                'consumable-movements.view',
            ]);
        }

        // Supervisor CSSD
        $supervisor = Role::where('name', 'Supervisor CSSD')->first();
        if ($supervisor) {
            $supervisor->syncPermissions([
                'dashboard.view',
                'units.view',
                'instruments.view', 'instrument-items.view',
                'tray-templates.view', 'tray-templates.create', 'tray-templates.edit',
                'trays.view', 'trays.create', 'trays.edit',
                'sterilizers.view',
                'storage-racks.view',
                'consumables.view', 'consumable-stocks.view',
                'consumable-movements.view', 'consumable-movements.create',
            ]);
        }

        // Operator CSSD
        $operator = Role::where('name', 'Operator CSSD')->first();
        if ($operator) {
            $operator->syncPermissions([
                'dashboard.view',
                'instruments.view', 'instrument-items.view',
                'tray-templates.view',
                'trays.view',
                'sterilizers.view',
                'storage-racks.view',
                'consumables.view', 'consumable-stocks.view',
                'consumable-movements.view', 'consumable-movements.create',
            ]);
        }

        // Teknisi
        $teknisi = Role::where('name', 'Teknisi')->first();
        if ($teknisi) {
            $teknisi->syncPermissions([
                'dashboard.view',
                'instruments.view', 'instrument-items.view',
                'instrument-items.edit',
                'sterilizers.view',
            ]);
        }
    }
}