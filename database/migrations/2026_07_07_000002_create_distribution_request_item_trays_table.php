<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu item permintaan (quantity N) → N tray fisik.
        Schema::create('distribution_request_item_trays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained('distribution_request_items')
                ->cascadeOnDelete();
            $table->foreignId('tray_id')
                ->constrained('trays')
                ->cascadeOnDelete();
            $table->foreignId('issued_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'tray_id']);
        });

        // Pindahkan assignment tunggal yang sudah ada ke pivot.
        DB::table('distribution_request_items')
            ->whereNotNull('tray_id')
            ->orderBy('id')
            ->each(function ($item) {
                DB::table('distribution_request_item_trays')->insert([
                    'item_id' => $item->id,
                    'tray_id' => $item->tray_id,
                    'issued_at' => $item->updated_at ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('distribution_request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tray_id');
        });
    }

    public function down(): void
    {
        Schema::table('distribution_request_items', function (Blueprint $table) {
            $table->foreignId('tray_id')->nullable()->constrained('trays')->nullOnDelete();
        });

        // Kembalikan satu tray pertama per item (best-effort).
        DB::table('distribution_request_item_trays')
            ->orderBy('id')
            ->get()
            ->groupBy('item_id')
            ->each(function ($rows, $itemId) {
                DB::table('distribution_request_items')
                    ->where('id', $itemId)
                    ->update(['tray_id' => $rows->first()->tray_id]);
            });

        Schema::dropIfExists('distribution_request_item_trays');
    }
};
