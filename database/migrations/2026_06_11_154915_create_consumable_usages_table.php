<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consumable_id')
                  ->constrained('consumables')
                  ->cascadeOnDelete();
            $table->morphs('usageable');
            // usageable_type: App\Models\Tray | App\Models\SterilizationBatch
            // usageable_id
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->foreignId('used_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('used_at');
            $table->timestamps();
            $table->auditColumnsWithDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_usages');
    }
};