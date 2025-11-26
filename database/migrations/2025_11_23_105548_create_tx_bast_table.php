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
        Schema::create('tx_bast', function (Blueprint $table) {
            $table->bigIncrements('bast_id');
            $table->unsignedBigInteger('sp3k_id')->index('idx_sp3k_id');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id');
            $table->char('tahun_anggaran', 4)->index('idx_tahun_anggaran');
            $table->date('tanggal_bast');
            $table->unsignedSmallInteger('sequence')->default(1)->comment('Urutan BAST untuk SP3K yang sama');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('sp3k_id')
                ->references('sp3k_id')
                ->on('tx_sp3k')
                ->onDelete('restrict');

            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_bast');
    }
};
