<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Masa simpan steril (hari) per rumah sakit — dipakai menghitung kedaluwarsa.
        Schema::table('hospitals', function (Blueprint $table) {
            $table->unsignedSmallInteger('sterile_shelf_life_days')->default(30)->after('phone');
        });

        // Bukti sterilisasi tingkat-load.
        Schema::table('sterilization_batches', function (Blueprint $table) {
            $table->string('method')->nullable()->after('sterilizer_id');      // STEAM | EO | PLASMA | FORMALIN
            $table->string('load_number')->nullable()->after('batch_number');  // untuk recall
            $table->string('bi_result')->nullable()->after('duration_minutes'); // PENDING | NEGATIVE | POSITIVE
            $table->string('bowie_dick_result')->nullable()->after('bi_result'); // PASS | FAIL | NA
        });

        // Indikator kimia per tray (per pak).
        Schema::table('sterilization_batch_items', function (Blueprint $table) {
            $table->string('ci_result')->nullable()->after('result'); // PASS | FAIL
        });

        // Status steril terkini pada tray (untuk guard distribusi & scheduler kedaluwarsa).
        Schema::table('trays', function (Blueprint $table) {
            $table->timestamp('sterile_at')->nullable()->after('assembled_at');
            $table->timestamp('expiry_at')->nullable()->after('sterile_at');
            $table->string('current_load_number')->nullable()->after('expiry_at');
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn('sterile_shelf_life_days');
        });
        Schema::table('sterilization_batches', function (Blueprint $table) {
            $table->dropColumn(['method', 'load_number', 'bi_result', 'bowie_dick_result']);
        });
        Schema::table('sterilization_batch_items', function (Blueprint $table) {
            $table->dropColumn('ci_result');
        });
        Schema::table('trays', function (Blueprint $table) {
            $table->dropColumn(['sterile_at', 'expiry_at', 'current_load_number']);
        });
    }
};
