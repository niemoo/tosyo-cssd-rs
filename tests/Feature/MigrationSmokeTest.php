<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_migrations_run_on_sqlite(): void
    {
        $this->assertTrue(Schema::hasTable('trays'));
        $this->assertTrue(Schema::hasTable('tray_lifecycle_events'));
        $this->assertTrue(Schema::hasTable('distribution_request_item_trays'));

        // Kolom yang ditambahkan migrasi kita
        $this->assertTrue(Schema::hasColumn('trays', 'barcode'));
        $this->assertTrue(Schema::hasColumn('trays', 'expiry_at'));
        $this->assertTrue(Schema::hasColumn('sterilization_batches', 'bi_result'));

        // Kolom yang DIHAPUS migrasi pivot (dropConstrainedForeignId)
        $this->assertFalse(
            Schema::hasColumn('distribution_request_items', 'tray_id'),
            'Kolom tray_id seharusnya sudah dihapus oleh migrasi pivot'
        );
    }
}
