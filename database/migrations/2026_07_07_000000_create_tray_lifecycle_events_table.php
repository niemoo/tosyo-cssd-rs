<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tray_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tray_id')->constrained()->cascadeOnDelete();

            $table->string('event_type');
            // ASSEMBLE_START | PACKAGE | LOAD | STERILIZE_PASS | STERILIZE_FAIL
            // | ISSUE | RETURN | RETURN_DAMAGED | EXPIRE | REPROCESS | RETIRE
            $table->string('from_status')->nullable();
            $table->string('to_status');

            // Menautkan peristiwa ke transaksi aslinya:
            // SterilizationBatch | DistributionRequest | TrayReturn | DecontaminationCycle
            $table->nullableMorphs('reference');

            // Bukti bebas-bentuk: bi_result, ci_result, sterile_at, expiry_at,
            // lot_no, condition, wash_method, missing_items, dll.
            $table->json('context')->nullable();

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('performed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tray_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tray_lifecycle_events');
    }
};
