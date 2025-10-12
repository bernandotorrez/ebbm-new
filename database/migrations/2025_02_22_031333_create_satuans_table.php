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
        Schema::create('ms_satuan', function (Blueprint $table) {
            $table->bigIncrements('satuan_id');
            $table->string('satuan', 50);
            $table->softDeletes();
            $table->timestamps();

            $table->index('satuan', 'idx_satuan_ms_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_satuan');
    }
};
