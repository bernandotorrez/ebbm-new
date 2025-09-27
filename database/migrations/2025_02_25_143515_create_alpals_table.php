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
        Schema::create('alpals', function (Blueprint $table) {
            $table->id('alpal_id');
            $table->unsignedBigInteger('kantor_sar_id')->index('idx_kantor_sar_id');
            $table->unsignedBigInteger('tbbm_id')->index('idx_tbbm_id');
            $table->unsignedBigInteger('pos_sandar_id')->index('idx_pos_sandar_id');
            $table->string('alpal', 100)->index('idx_alpal');
            $table->decimal('ukuran', 10, 2);
            $table->decimal('kapasitas', 10, 2);
            $table->decimal('rob', 10, 2);
            $table->foreign('kantor_sar_id')
            ->references('kantor_sar_id')
            ->on('kantor_sars')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('tbbm_id')
            ->references('tbbm_id')
            ->on('tbbms')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreign('pos_sandar_id')
            ->references('pos_sandar_id')
            ->on('pos_sandars')
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
        Schema::dropIfExists('alpals');
    }
};
