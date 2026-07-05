<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('tray_templates')
                  ->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('ASSEMBLING');
            // ASSEMBLING | READY | IN_STERILIZATION | STERILE | IN_USE | RETURNED | NEEDS_REPROCESSING
            $table->foreignId('current_rack_id')
                  ->nullable()
                  ->constrained('storage_racks')
                  ->nullOnDelete();
            $table->foreignId('assembled_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('assembled_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->auditColumnsWithDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trays');
    }
};