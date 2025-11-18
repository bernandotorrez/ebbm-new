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
        Schema::create('tx_alpal', function (Blueprint $table) {
            $table->bigIncrements('alpal_id');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id_tx_alpal');
            $table->unsignedBigInteger('tbbm_id')->index('idx_tbbm_id_tx_alpal');
            $table->unsignedBigInteger('pos_sandar_id')->index('idx_pos_sandar_id_tx_alpal');
            $table->string('alpal', 100);
            $table->unsignedDecimal('ukuran', 10, 2, true); // unsigned decimal
            $table->unsignedDecimal('kapasitas', 10, 2, true); // unsigned decimal
            $table->unsignedDecimal('rob', 10, 2, true); // unsigned decimal
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tx_alpal');
    }
};
