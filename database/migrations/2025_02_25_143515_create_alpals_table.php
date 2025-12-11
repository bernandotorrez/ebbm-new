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
            $table->string('kode_alut', 3)->nullable()->index('idx_kode_alut');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id_tx_alpal');
            $table->unsignedBigInteger('golongan_bbm_id');
            $table->unsignedBigInteger('pos_sandar_id')->index('idx_pos_sandar_id_tx_alpal');
            $table->string('alpal', 100);
            $table->decimal('ukuran', 10, 2, true); // unsigned decimal
            $table->decimal('kapasitas', 10, 2, true); // unsigned decimal
            $table->decimal('rob', 10, 2, true); // unsigned decimal
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            $table->foreign('kantor_sar_id')->references('kantor_sar_id')->on('ms_kantor_sar')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('golongan_bbm_id')->references('golongan_bbm_id')->on('ms_golongan_bbm')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('pos_sandar_id')->references('pos_sandar_id')->on('ms_pos_sandar')->noActionOnDelete()->noActionOnUpdate();
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
