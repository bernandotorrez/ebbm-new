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
        Schema::create('ms_pelumas', function (Blueprint $table) {
            $table->bigIncrements('pelumas_id');
            $table->string('nama_pelumas', 200);
            $table->unsignedBigInteger('pack_id');
            $table->unsignedBigInteger('kemasan_id');
            $table->char('tahun', 4);
            $table->unsignedInteger('isi')->default(0);
            $table->unsignedDecimal('harga', 20, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            // Add foreign key constraints
            $table->foreign('pack_id')->references('pack_id')->on('ms_pack');
            $table->foreign('kemasan_id')->references('kemasan_id')->on('ms_kemasan');
            
            // Add indexes
            $table->index('pack_id', 'idx_pack_id');
            $table->index('kemasan_id', 'idx_kemasan_id');
            $table->index('isi', 'idx_isi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_pelumas');
    }
};