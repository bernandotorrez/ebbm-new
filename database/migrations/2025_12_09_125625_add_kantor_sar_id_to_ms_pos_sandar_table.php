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
        Schema::table('ms_pos_sandar', function (Blueprint $table) {
            $table->unsignedBigInteger('kantor_sar_id')->nullable()->after('pos_sandar_id');
            $table->foreign('kantor_sar_id')->references('kantor_sar_id')->on('ms_kantor_sar')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_pos_sandar', function (Blueprint $table) {
            $table->dropForeign(['kantor_sar_id']);
            $table->dropColumn('kantor_sar_id');
        });
    }
};
