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
        Schema::create('ms_tbbm', function (Blueprint $table) {
            $table->bigIncrements('tbbm_id');
            $table->unsignedBigInteger('kota_id')->index('idx_kota_id_ms_tbbm');
            $table->string('plant', 5);
            $table->string('depot', 50);
            $table->decimal('pbbkb', 5, 2, true); // unsigned decimal
            $table->char('ship_to', 6);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_tbbm');
    }
};
