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
        Schema::create('pemakaians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id');
            $table->unsignedBigInteger('alpal_id')->index('idx_alpal_id');
            $table->unsignedBigInteger('bekal_id')->index('idx_bekal_id');
            $table->date('tanggal_pakai');
            $table->smallInteger('qty', false, true);
            $table->text('keterangan');
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
        Schema::dropIfExists('pemakaians');
    }
};
