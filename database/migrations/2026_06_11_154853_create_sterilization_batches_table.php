<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sterilization_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sterilizer_id')
                  ->constrained('sterilizers')
                  ->cascadeOnDelete();
            $table->string('batch_number');
            $table->string('status')->default('PENDING');
            // PENDING | IN_PROGRESS | COMPLETED | FAILED
            $table->decimal('temperature', 5, 2)->nullable(); // °C
            $table->decimal('pressure', 5, 2)->nullable();    // kPa / bar
            $table->integer('duration_minutes')->nullable();
            $table->foreignId('operator_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->auditColumnsWithDelete();

            $table->unique(['hospital_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sterilization_batches');
    }
};