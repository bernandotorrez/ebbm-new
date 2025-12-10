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
        Schema::create('tx_sp3m_lampiran', function (Blueprint $table) {
            $table->bigIncrements('lampiran_id');
            $table->unsignedBigInteger('sp3m_id')->index('idx_sp3m_id_tx_sp3m_lampiran');
            $table->string('nama_file', 255);
            $table->text('file_path');
            $table->string('keterangan', 500)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('sp3m_id')->references('sp3m_id')->on('tx_sp3m')->cascadeOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_sp3m_lampiran');
    }
};
