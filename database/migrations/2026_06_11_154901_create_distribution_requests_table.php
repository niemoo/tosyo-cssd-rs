<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->cascadeOnDelete();
            $table->string('request_number');
            $table->string('status')->default('DRAFT');
            // DRAFT | PENDING_APPROVAL | APPROVED | REJECTED | IN_PROCESS | FULFILLED | CLOSED
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('fulfilled_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->auditColumnsWithDelete();

            $table->unique(['hospital_id', 'request_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_requests');
    }
};