<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Users dulu sebelum semua yang lain
            // karena created_by/updated_by FK ke users
            UserSeeder::class,

            // 2. Spatie roles & permissions
            RoleSeeder::class,
            PermissionSeeder::class,

            // 3. Master data utama
            HospitalSeeder::class,
            HospitalUserSeeder::class,
            UnitSeeder::class,

            // 4. Instrumen
            InstrumentCategorySeeder::class,
            InstrumentSeeder::class,

            // 5. Tray (harus sebelum instrument items)
            TrayTemplateSeeder::class,  // includes tray_template_items
            TraySeeder::class,

            // 6. Instrument items (butuh tray sudah ada)
            InstrumentItemSeeder::class,

            // 7. Fasilitas CSSD
            SterilizerSeeder::class,
            StorageRackSeeder::class,

            // 8. Consumables
            ConsumableCategorySeeder::class,
            ConsumableSeeder::class,
            ConsumableStockSeeder::class,

            TraySeeder::class,
            SterilizationBatchSeeder::class,
            DistributionRequestSeeder::class,
            TrayReturnSeeder::class,
        ]);
    }
}