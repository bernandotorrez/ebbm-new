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
            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->noActionOnDelete()
                ->noActionOnUpdate();
                
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
            $table->dropForeign(['kantor_sar_id']);
            $table->dropForeign(['tx_alpal_id']);
        });
    }
};
