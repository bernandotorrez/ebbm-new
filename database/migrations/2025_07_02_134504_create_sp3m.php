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
        Schema::create('tx_sp3m', function (Blueprint $table) {
            $table->bigIncrements('sp3m_id');
            $table->unsignedBigInteger('alpal_id')->index('idx_alpal_id_tx_sp3m');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id_tx_sp3m');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id_tx_sp3m');
            $table->unsignedBigInteger('tbbm_id')->nullable();
            $table->string('nomor_sp3m', 200)->index('idx_nomor_sp3m');
            $table->date('tanggal_sp3m')->nullable();
            $table->char('tahun_anggaran', 4)->index('idx_tahun_anggaran');
            $table->char('tw', 1)->index('idx_tw');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('sisa_qty');
            $table->decimal('harga_satuan', 20, 2); // unsigned decimal
            $table->decimal('jumlah_harga', 20, 2); // unsigned decimal
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            $table->foreign('kantor_sar_id')->references('kantor_sar_id')->on('ms_kantor_sar')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('alpal_id')->references('alpal_id')->on('tx_alpal')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('bekal_id')->references('bekal_id')->on('ms_bekal')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('tbbm_id')->references('tbbm_id')->on('ms_tbbm')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_sp3m');
    }
};
