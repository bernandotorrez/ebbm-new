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
        Schema::create('tx_pemakaian', function (Blueprint $table) {
            $table->bigIncrements('pemakaian_id');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id_tx_pemakaian');
            $table->unsignedBigInteger('alpal_id')->index('idx_alpal_id_tx_pemakaian');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id_tx_pemakaian');
            $table->date('tanggal_pakai')->index('idx_tanggal_pakai_tx_pemakaian');
            $table->string('data_kegiatan', 50)->nullable();
            $table->unsignedSmallInteger('qty');
            $table->text('keterangan');
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('kantor_sar_id')->references('kantor_sar_id')->on('ms_kantor_sar')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('alpal_id')->references('alpal_id')->on('tx_alpal')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('bekal_id')->references('bekal_id')->on('ms_bekal')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_pemakaian');
    }
};
