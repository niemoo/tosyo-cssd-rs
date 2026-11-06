<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trays', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('trays', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });
    }
};
