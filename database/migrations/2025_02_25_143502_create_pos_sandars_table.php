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
        Schema::create('ms_pos_sandar', function (Blueprint $table) {
            $table->bigIncrements('pos_sandar_id');
            $table->unsignedBigInteger('kantor_sar_id')->nullable();
            $table->string('pos_sandar', 50);
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');

            $table->index('pos_sandar', 'idx_pos_sandar_ms_pos_sandar');
            
            $table->foreign('kantor_sar_id')->references('kantor_sar_id')->on('ms_kantor_sar')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_pos_sandar');
    }
};
