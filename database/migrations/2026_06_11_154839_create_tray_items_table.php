<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tray_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tray_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_item_id')
                  ->constrained('instrument_items')
                  ->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tray_id', 'instrument_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tray_items');
    }
};