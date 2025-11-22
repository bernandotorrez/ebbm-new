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
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->unsignedBigInteger('golongan_bbm_id')->after('kantor_sar_id');
            $table->foreign('golongan_bbm_id')->references('golongan_bbm_id')->on('ms_golongan_bbm')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->dropForeign(['golongan_bbm_id']);
            $table->dropColumn('golongan_bbm_id');
        });
    }
};
