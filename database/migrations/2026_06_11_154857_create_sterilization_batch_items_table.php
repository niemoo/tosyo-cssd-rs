<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sterilization_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')
                  ->constrained('sterilization_batches')
                  ->cascadeOnDelete();
            $table->foreignId('tray_id')
                  ->constrained('trays')
                  ->cascadeOnDelete();
            $table->string('result')->default('PENDING');
            // PENDING | PASSED | FAILED
            $table->text('failure_notes')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'tray_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sterilization_batch_items');
    }
};