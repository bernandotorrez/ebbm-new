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
        // Add foreign key constraints after all tables have been created
        Schema::table('ms_user', function (Blueprint $table) {
            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('ms_tbbm', function (Blueprint $table) {
            $table->foreign('kota_id')
                ->references('kota_id')
                ->on('ms_kota')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('ms_bekal', function (Blueprint $table) {
            $table->foreign('golongan_bbm_id')
                ->references('golongan_bbm_id')
                ->on('ms_golongan_bbm')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            $table->foreign('satuan_id')
                ->references('satuan_id')
                ->on('ms_satuan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('tx_pagu', function (Blueprint $table) {
            $table->foreign('golongan_bbm_id')
                ->references('golongan_bbm_id')
                ->on('ms_golongan_bbm')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('tbbm_id')
                ->references('tbbm_id')
                ->on('ms_tbbm')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('pos_sandar_id')
                ->references('pos_sandar_id')
                ->on('ms_pos_sandar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('alpal_id')
                ->references('alpal_id')
                ->on('tx_alpal')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('bekal_id')
                ->references('bekal_id')
                ->on('ms_bekal')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('tx_do', function (Blueprint $table) {
            $table->foreign('sp3m_id')
                ->references('sp3m_id')
                ->on('tx_sp3m')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('tbbm_id')
                ->references('tbbm_id')
                ->on('ms_tbbm')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('tx_pemakaian', function (Blueprint $table) {
            $table->foreign('kantor_sar_id')
                ->references('kantor_sar_id')
                ->on('ms_kantor_sar')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('alpal_id')
                ->references('alpal_id')
                ->on('tx_alpal')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->foreign('bekal_id')
                ->references('bekal_id')
                ->on('ms_bekal')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('tx_pemakaian', function (Blueprint $table) {
            $table->dropForeign(['kantor_sar_id']);
            $table->dropForeign(['alpal_id']);
            $table->dropForeign(['bekal_id']);
        });

        Schema::table('tx_do', function (Blueprint $table) {
            $table->dropForeign(['sp3m_id']);
            $table->dropForeign(['tbbm_id']);
        });

        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->dropForeign(['kantor_sar_id']);
            $table->dropForeign(['alpal_id']);
            $table->dropForeign(['bekal_id']);
        });

        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->dropForeign(['kantor_sar_id']);
            $table->dropForeign(['tbbm_id']);
            $table->dropForeign(['pos_sandar_id']);
        });

        Schema::table('tx_pagu', function (Blueprint $table) {
            $table->dropForeign(['golongan_bbm_id']);
        });

        Schema::table('ms_bekal', function (Blueprint $table) {
            $table->dropForeign(['golongan_bbm_id']);
            $table->dropForeign(['satuan_id']);
        });

        Schema::table('ms_tbbm', function (Blueprint $table) {
            $table->dropForeign(['kota_id']);
        });

        Schema::table('ms_user', function (Blueprint $table) {
            $table->dropForeign(['kantor_sar_id']);
        });
    }
};
