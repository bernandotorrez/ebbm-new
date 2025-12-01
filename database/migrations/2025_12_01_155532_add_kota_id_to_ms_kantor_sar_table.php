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
        Schema::table('ms_kantor_sar', function (Blueprint $table) {
            $table->unsignedBigInteger('kota_id')->nullable()->after('kantor_sar')->index('idx_kota_id_kantor_sar');
            $table->foreign('kota_id')->references('kota_id')->on('ms_kota')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_kantor_sar', function (Blueprint $table) {
            $table->dropForeign(['kota_id']);
            $table->dropIndex('idx_kota_id_kantor_sar');
            $table->dropColumn('kota_id');
        });
    }
};
