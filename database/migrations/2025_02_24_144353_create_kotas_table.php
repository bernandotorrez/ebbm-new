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
        Schema::create('ms_kota', function (Blueprint $table) {
            $table->bigIncrements('kota_id');
            $table->string('kota', 50);
            $table->softDeletes();
            $table->timestamps();

            $table->index('kota', 'idx_kota_ms_kota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_kota');
    }
};
