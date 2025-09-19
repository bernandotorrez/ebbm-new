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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id('do_id');
            $table->unsignedBigInteger('sp3m_id')->index('idx_sp3m_id');
            $table->unsignedBigInteger('tbbm_id')->index('idx_tbbm_id');
            $table->date('tanggal_do');
            $table->char('tahun_anggaran', 4);
            $table->string('nomor_do', 200)->index('idx_nomor_do');
            $table->unsignedInteger('qty');
            $table->decimal('harga_satuan', 20, 2)->unsigned();
            $table->decimal('ppn', 5, 2)->unsigned();
            $table->decimal('pbbkb', 10, 2)->unsigned();
            $table->decimal('jumlah_harga', 20, 2)->unsigned();
            $table->text('file_upload_do');
            $table->text('file_upload_laporan');
            $table->foreign('sp3m_id')
            ->references('sp3m_id')
            ->on('sp3ms')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('tbbm_id')
            ->references('tbbm_id')
            ->on('tbbms')
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
        Schema::dropIfExists('delivery_orders');
    }
};
