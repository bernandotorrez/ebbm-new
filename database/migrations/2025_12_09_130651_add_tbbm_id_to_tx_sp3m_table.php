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
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->unsignedBigInteger('tbbm_id')->nullable()->after('bekal_id');
            $table->foreign('tbbm_id')->references('tbbm_id')->on('ms_tbbm')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->dropForeign(['tbbm_id']);
            $table->dropColumn('tbbm_id');
        });
    }
};
