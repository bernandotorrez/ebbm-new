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
        Schema::create('tx_bast', function (Blueprint $table) {
            $table->bigIncrements('bast_id');
            $table->unsignedBigInteger('sp3k_id')->index('idx_sp3k_id');
            $table->date('tanggal_bast');
            $table->unsignedTinyInteger('bast_ke')->comment('BAST ke berapa?');
            $table->enum('sudah_diterima_semua', ['0', '1'])->default('0')
                ->comment('Kalau 0 berarti masih terutang/outstanding, kalau 1 berarti sudah selesai BAST nya');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('sp3k_id')
                ->references('sp3k_id')
                ->on('tx_sp3k')
                ->noActionOnDelete()
                ->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_bast');
    }
};
