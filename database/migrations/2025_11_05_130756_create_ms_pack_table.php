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
        Schema::create('ms_pack', function (Blueprint $table) {
            $table->bigIncrements('pack_id');
            $table->string('nama_pack', 50);
            $table->softDeletes();
            $table->timestamps();

            $table->index('nama_pack', 'idx_nama_pack');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_pack');
    }
};
