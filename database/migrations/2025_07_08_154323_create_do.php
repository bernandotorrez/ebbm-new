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
            $table->unsignedBigInteger('tbbm_id')->index('idx_tbbm_id_tx_do');
            $table->unsignedBigInteger('harga_bekal_id')->index('idx_harga_bekal_id_tx_do');
            $table->string('nomor_do', 200)->index('idx_nomor_do_tx_do');
            $table->char('tahun_anggaran', 4);
            $table->date('tanggal_do');
            $table->unsignedInteger('qty');
            // $table->decimal('harga_satuan', 20, 2, true)->unsigned(); // unsigned decimal
            $table->decimal('ppn', 10, 2, true)->unsigned(); // unsigned decimal
            $table->decimal('pbbkb', 10, 2, true)->unsigned(); // unsigned decimal
            $table->decimal('jumlah_harga', 20, 2, true)->unsigned(); // unsigned decimal
            $table->text('file_upload_do');
            $table->text('file_upload_laporan');
            $table->softDeletes();
            $table->timestamps();
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
