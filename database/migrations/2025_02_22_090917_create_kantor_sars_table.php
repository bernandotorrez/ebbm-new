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
        Schema::create('ms_kantor_sar', function (Blueprint $table) {
            $table->bigIncrements('kantor_sar_id');
            $table->string('kantor_sar', 50);
            $table->unsignedBigInteger('kota_id')->nullable()->index('idx_kota_id_kantor_sar');
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('kantor_sar', 'idx_kantor_sar_ms_kantor_sar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_kantor_sar');
    }
};
