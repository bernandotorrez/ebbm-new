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
            $table->string('nomor_sp3m', 200)->index('idx_nomor_sp3m_tx_sp3m');
            $table->char('tahun_anggaran', 4)->index('idx_tahun_anggaran');
            $table->char('tw', 1)->index('idx_tw');
            $table->unsignedInteger('qty');
            $table->decimal('harga_satuan', 20, 2, true); // unsigned decimal
            $table->decimal('jumlah_harga', 20, 2, true); // unsigned decimal
            $table->text('file_upload_sp3m');
            $table->text('file_upload_kelengkapan_sp3m');
            $table->softDeletes();
            $table->timestamps();
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
