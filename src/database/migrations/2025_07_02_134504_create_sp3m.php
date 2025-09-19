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
        Schema::create('sp3ms', function (Blueprint $table) {
            $table->id('sp3m_id');
            $table->string('nomor_sp3m', 200)->index('idx_nomor_sp3m');
            $table->char('tahun_anggaran', 4);
            $table->string('tw', 25);
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id');
            $table->unsignedBigInteger('alpal_id')->index('idx_alpal_id');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id');
            $table->unsignedInteger('qty');
            $table->decimal('harga_satuan', 20, 2)->unsigned();
            $table->decimal('jumlah_harga', 20, 2)->unsigned();
            $table->text('file_upload_sp3m');
            $table->text('file_upload_kelengkapan_sp3m');
            $table->foreign('kantor_sar_id')
            ->references('kantor_sar_id')
            ->on('kantor_sars')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('alpal_id')
            ->references('alpal_id')
            ->on('alpals')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('bekal_id')
            ->references('bekal_id')
            ->on('bekals')
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
        Schema::dropIfExists('sp3m');
    }
};
