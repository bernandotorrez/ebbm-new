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
        Schema::create('ms_bekal', function (Blueprint $table) {
            $table->bigIncrements('bekal_id');
            $table->unsignedBigInteger('golongan_bbm_id')->index('idx_golongan_bbm_id_ms_bekal');
            $table->unsignedBigInteger('satuan_id')->index('idx_satuan_id_ms_satuan');
            $table->string('bekal', 50)->index('idx_bekal_ms_bekal');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_bekal');
    }
};
