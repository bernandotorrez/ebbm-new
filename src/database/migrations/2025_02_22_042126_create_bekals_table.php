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
        Schema::create('bekals', function (Blueprint $table) {
            $table->id('bekal_id');
            $table->unsignedBigInteger('golongan_bbm_id')->index('idx_golongan_bbm_id');
            $table->unsignedBigInteger('satuan_id')->index('idx_satuan_id');
            $table->string('bekal', 50)->index('idx_bekal');
            $table->foreign('satuan_id')
            ->references('satuan_id')
            ->on('satuans')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('golongan_bbm_id')
            ->references('golongan_bbm_id')
            ->on('golongan_bbms')
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
        Schema::dropIfExists('bekals');
    }
};
