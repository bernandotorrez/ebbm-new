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
        Schema::create('pagus', function (Blueprint $table) {
            $table->id('pagu_id');
            $table->unsignedBigInteger('golongan_bbm_id')->index('idx_golongan_bbm_id');
            $table->decimal('nilai_pagu', 20, 2);
            $table->string('tahun_anggaran', 4);
            $table->string('dasar', 50);
            $table->date('tanggal');
            $table->foreign('golongan_bbm_id')
            ->references('golongan_bbm_id')
            ->on('golongan_bbms')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagus');
    }
};
