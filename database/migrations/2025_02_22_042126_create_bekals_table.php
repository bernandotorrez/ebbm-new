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
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            $table->foreign('golongan_bbm_id')->references('golongan_bbm_id')->on('ms_golongan_bbm')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('satuan_id')->references('satuan_id')->on('ms_satuan')->noActionOnDelete()->noActionOnUpdate();
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
