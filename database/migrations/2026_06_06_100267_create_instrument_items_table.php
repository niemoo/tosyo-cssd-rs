<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instrument_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();
            $table->string('serial_number')->unique()->nullable();
            $table->string('code')->unique();
            $table->string('barcode')->unique()->nullable();
            $table->string('rfid_tag')->unique()->nullable();
            $table->string('condition')->default('GOOD');
            // GOOD | DAMAGED | UNDER_REPAIR | RETIRED
            $table->integer('total_cycles')->default(0);
            $table->foreignId('current_tray_id')
                ->nullable()
                ->constrained('trays')
                ->nullOnDelete();
            $table->date('purchased_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->auditColumnsWithDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrument_items');
    }
};
