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
            $table->unsignedBigInteger('wilayah_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('kota', 'idx_kota_ms_kota');
            
            $table->foreign('wilayah_id')
                ->references('wilayah_id')
                ->on('ms_wilayah')
                ->noActionOnDelete()
                ->noActionOnUpdate();
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
