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
        Schema::create('ms_pos_sandar', function (Blueprint $table) {
            $table->bigIncrements('pos_sandar_id');
            $table->string('pos_sandar', 50);
            $table->softDeletes();
            $table->timestamps();

            $table->index('pos_sandar', 'idx_pos_sandar_ms_pos_sandar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_pos_sandar');
    }
};
