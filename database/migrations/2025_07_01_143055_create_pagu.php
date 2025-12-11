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
        Schema::create('tx_pagu', function (Blueprint $table) {
            $table->bigIncrements('pagu_id');
            $table->unsignedBigInteger('golongan_bbm_id')->index('idx_golongan_bbm_tx_pagu');
            $table->decimal('nilai_pagu', 20, 2, true); // unsigned decimal
            $table->string('tahun_anggaran', 4);
            $table->string('dasar', 50);
            $table->date('tanggal');
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            $table->foreign('golongan_bbm_id')->references('golongan_bbm_id')->on('ms_golongan_bbm')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_pagu');
    }
};
