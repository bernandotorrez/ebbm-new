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
        // Add user tracking fields to all ms_ tables
        $ms_tables = [
            'ms_user',
            'ms_bekal',
            'ms_golongan_bbm',
            'ms_harga_bekal',
            'ms_kantor_sar',
            'ms_kemasan',
            'ms_kota',
            'ms_pack',
            'ms_pelumas',
            'ms_pos_sandar',
            'ms_satuan',
            'ms_tbbm',
            'ms_wilayah',
        ];

        foreach ($ms_tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                });
            }
        }

        // Add user tracking fields to all tx_ tables
        $tx_tables = [
            'tx_alpal',
            'tx_do',
            'tx_pagu',
            'tx_pemakaian',
            'tx_sp3m',
        ];

        foreach ($tx_tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user tracking fields from all ms_ tables
        $ms_tables = [
            'ms_user',
            'ms_bekal',
            'ms_golongan_bbm',
            'ms_harga_bekal',
            'ms_kantor_sar',
            'ms_kemasan',
            'ms_kota',
            'ms_pack',
            'ms_pelumas',
            'ms_pos_sandar',
            'ms_satuan',
            'ms_tbbm',
            'ms_wilayah',
        ];

        foreach ($ms_tables as $tableName) {
            if (Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
                });
            }
        }

        // Remove user tracking fields from all tx_ tables
        $tx_tables = [
            'tx_alpal',
            'tx_do',
            'tx_pagu',
            'tx_pemakaian',
            'tx_sp3m',
        ];

        foreach ($tx_tables as $tableName) {
            if (Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
                });
            }
        }
    }
};
