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
            $table->unsignedBigInteger('kantor_sar_id');
            $table->string('nomor_sp3k', 200)->unique();
            $table->char('tahun_anggaran', 4);
            $table->date('tanggal_sp3k');
            $table->char('tw', 1);
            $table->unsignedSmallInteger('jumlah_qty');
            $table->decimal('jumlah_harga', 20, 2)->unsigned();
            $table->unsignedMediumInteger('jumlah_liter');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->onDelete('restrict');

            $table->index('kantor_sar_id', 'idx_kantor_sar_id');
            $table->index('tahun_anggaran', 'idx_tahun_anggaran');
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
