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
        Schema::table('tx_do', function (Blueprint $table) {
            $table->foreign('harga_bekal_id')
                ->references('harga_bekal_id')
                ->on('ms_harga_bekal')
                ->noActionOnDelete()
                ->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_do', function (Blueprint $table) {
            $table->dropForeign(['harga_bekal_id']);
        });
    }
};
