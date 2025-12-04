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
        Schema::create('ms_harga_bekal', function (Blueprint $table) {
            $table->id('harga_bekal_id');
            $table->unsignedBigInteger('wilayah_id')->index('idx_wilayah_id');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id');
            $table->decimal('harga', 20, 2);
            $table->date('tanggal_update')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('wilayah_id')->references('wilayah_id')->on('ms_wilayah')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('bekal_id')->references('bekal_id')->on('ms_bekal')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_harga_bekal');
    }
};
