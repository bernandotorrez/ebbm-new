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
        Schema::create('tx_sp3k', function (Blueprint $table) {
            $table->bigIncrements('sp3k_id');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id');
            $table->string('nomor_sp3k', 200)->index('idx_nomor_sp3k');
            $table->char('tahun_anggaran', 4)->index('idx_tahun_anggaran');
            $table->date('tanggal_sp3k');
            $table->char('tw', 1)->index('idx_tw');
            $table->unsignedInteger('jumlah_qty');
            $table->enum('bast_sudah_diterima_semua', ['0', '1'])->default('0')
                ->comment('Kalau 0 berarti masih terutang/outstanding, kalau 1 berarti sudah selesai BAST nya');
            $table->decimal('jumlah_harga', 20, 2)->unsigned();
            $table->unsignedInteger('jumlah_liter');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->noActionOnDelete()
                ->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_sp3k');
    }
};
