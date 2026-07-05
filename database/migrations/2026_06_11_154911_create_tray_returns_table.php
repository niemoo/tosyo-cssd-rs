<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tray_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distribution_request_id')
                  ->constrained('distribution_requests')
                  ->cascadeOnDelete();
            $table->foreignId('tray_id')
                  ->constrained('trays')
                  ->cascadeOnDelete();
            $table->foreignId('received_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('condition');
            // GOOD | DAMAGED | INCOMPLETE
            $table->text('missing_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('returned_at');
            $table->timestamps();
            $table->auditColumnsWithDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tray_returns');
    }
};