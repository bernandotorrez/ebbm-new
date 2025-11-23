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
        Schema::table('ms_user', function (Blueprint $table) {
            $table->unsignedBigInteger('tx_alpal_id')->nullable()->after('kantor_sar_id');
            
            $table->foreign('tx_alpal_id')
                ->references('alpal_id')
                ->on('tx_alpal')
                ->noActionOnDelete()
                ->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_user', function (Blueprint $table) {
            $table->dropForeign(['tx_alpal_id']);
            $table->dropColumn('tx_alpal_id');
        });
    }
};
