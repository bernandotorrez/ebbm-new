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
        Schema::create('tbbms', function (Blueprint $table) {
            $table->id('tbbm_id');
            $table->unsignedBigInteger('kota_id')->index('idx_kota_id');
            $table->string('plant', 4);
            $table->string('depot', 50);
            $table->decimal('pbbkb', 5, 2);
            $table->char('ship_to', 6);
            $table->foreign('kota_id')
            ->references('kota_id')
            ->on('kotas')
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
        Schema::dropIfExists('tbbms');
    }
};
