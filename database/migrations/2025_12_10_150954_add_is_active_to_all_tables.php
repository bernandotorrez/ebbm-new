<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // List of all tables that need is_active field
        $tables = [
            'ms_user',
            'ms_golongan_bbm',
            'ms_satuan',
            'ms_bekal',
            'ms_kantor_sar',
            'ms_wilayah',
            'ms_kota',
            'ms_tbbm',
            'ms_pos_sandar',
            'tx_alpal', // Alpal menggunakan tx_ bukan ms_
            'ms_pack',
            'ms_kemasan',
            'ms_pelumas',
            'ms_harga_bekal',
            'tx_pagu',
            'tx_sp3m',
            'tx_do',
            'tx_pemakaian',
            'tx_sp3k',
            'dx_sp3k',
            'tx_bast',
            'dx_bast',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->enum('is_active', ['0', '1'])->default('1')->after('deleted_at');
                });
                
                // Set existing records to active
                DB::table($table)->update(['is_active' => '1']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ms_user',
            'ms_golongan_bbm',
            'ms_satuan',
            'ms_bekal',
            'ms_kantor_sar',
            'ms_wilayah',
            'ms_kota',
            'ms_tbbm',
            'ms_pos_sandar',
            'ms_alpal',
            'ms_pack',
            'ms_kemasan',
            'ms_pelumas',
            'ms_harga_bekal',
            'tx_pagu',
            'tx_sp3m',
            'tx_do',
            'tx_pemakaian',
            'tx_sp3k',
            'dx_sp3k',
            'tx_bast',
            'dx_bast',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
        }
    }
};
