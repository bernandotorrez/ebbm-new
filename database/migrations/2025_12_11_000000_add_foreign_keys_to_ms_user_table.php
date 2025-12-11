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
        // Foreign key untuk kota_id di ms_kantor_sar
        Schema::table('ms_kantor_sar', function (Blueprint $table) {
            $table->foreign('kota_id', 'fk_ms_kantor_sar_kota_id')
                ->references('kota_id')
                ->on('ms_kota')
                ->onDelete('no action')
                ->onUpdate('no action');
        });

        // Foreign key untuk ms_user
        Schema::table('ms_user', function (Blueprint $table) {
            // Foreign key untuk kantor_sar_id
            $table->foreign('kantor_sar_id', 'fk_ms_user_kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->onDelete('no action')
                ->onUpdate('no action');

            // Foreign key untuk alpal_id
            $table->foreign('alpal_id', 'fk_ms_user_alpal_id')
                ->references('alpal_id')
                ->on('tx_alpal')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_user', function (Blueprint $table) {
            $table->dropForeign('fk_ms_user_kantor_sar_id');
            $table->dropForeign('fk_ms_user_alpal_id');
        });

        Schema::table('ms_kantor_sar', function (Blueprint $table) {
            $table->dropForeign('fk_ms_kantor_sar_kota_id');
        });
    }
};
