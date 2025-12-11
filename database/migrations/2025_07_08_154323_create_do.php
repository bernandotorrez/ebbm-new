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
        Schema::create('tx_do', function (Blueprint $table) {
            $table->bigIncrements('do_id');
            $table->unsignedBigInteger('sp3m_id')->index('idx_sp3m_id_tx_do');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id_tx_do');
            $table->unsignedBigInteger('kota_id')->index('idx_kota_id_tx_do');
            $table->unsignedBigInteger('harga_bekal_id')->nullable()->index('idx_harga_bekal_id_tx_do');
            $table->string('nomor_do', 200)->index('idx_nomor_do_tx_do');
            $table->char('tahun_anggaran', 4)->index('idx_tahun_anggaran');
            $table->date('tanggal_do');
            $table->decimal('qty', 20, 2)->unsigned()->default(0);
            $table->text('file_upload_do');
            $table->text('file_upload_laporan')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            $table->foreign('sp3m_id')->references('sp3m_id')->on('tx_sp3m')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('bekal_id')->references('bekal_id')->on('ms_bekal')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('kota_id')->references('kota_id')->on('ms_kota')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('harga_bekal_id')->references('harga_bekal_id')->on('ms_harga_bekal')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_do');
    }
};
