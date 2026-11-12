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
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->string('satuan', 20)->default('kg')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_sampah', function (Blueprint $table) {
            $table->enum('satuan', ['kg', 'gram'])->default('kg')->change();
        });
    }
};
