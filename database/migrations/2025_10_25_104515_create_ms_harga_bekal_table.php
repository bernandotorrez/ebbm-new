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
            $table->unsignedBigInteger('kota_id')->index('idx_kota_id');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id');
            $table->decimal('harga', 20, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('kota_id')->references('kota_id')->on('ms_kota')->onDelete('cascade');
            $table->foreign('bekal_id')->references('bekal_id')->on('ms_bekal')->onDelete('cascade');
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
