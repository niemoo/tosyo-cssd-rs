<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                  ->constrained('distribution_requests')
                  ->cascadeOnDelete();
            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('tray_templates')
                  ->nullOnDelete();
            $table->foreignId('tray_id')
                  ->nullable()
                  ->constrained('trays')
                  ->nullOnDelete();
            // template_id = hint jenis tray yang diminta
            // tray_id = tray fisik yang di-assign saat fulfill
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_request_items');
    }
};