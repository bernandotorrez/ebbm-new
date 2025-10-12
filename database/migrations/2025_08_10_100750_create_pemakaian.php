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
            $table->unsignedSmallInteger('qty');
            $table->text('keterangan');
            $table->softDeletes();
            $table->timestamps();
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
