<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_delivery_order_with_calculations AS
            SELECT 
                txdo.do_id,
                txdo.tanggal_do AS tanggal_isi,
                txsp3m.nomor_sp3m,
                txdo.nomor_do,
                txdo.qty,
                mhb.harga AS harga_per_liter,
                txdo.jumlah_harga,
                mks.kantor_sar,
                ta.alpal,
                -- PPN 11%
                CAST((txdo.jumlah_harga * 0.11) AS DECIMAL(20,2)) AS ppn_11,
                -- PPKB
                CAST((txdo.jumlah_harga * (mt.pbbkb / 100)) AS DECIMAL(20,2)) AS ppkb,
                -- Total
                CAST(
                    txdo.jumlah_harga + 
                    (txdo.jumlah_harga * 0.11) + 
                    (txdo.jumlah_harga * (mt.pbbkb / 100))
                    AS DECIMAL(20,2)
                ) AS total_ppn_ppkb,
                -- Pembulatan (ribuan terdekat)
                CAST(
                    (ROUND((txdo.jumlah_harga + 
                            txdo.jumlah_harga * 0.11 + 
                            txdo.jumlah_harga * (mt.pbbkb / 100)) / 1000) * 1000)
                    AS DECIMAL(20,2)
                ) AS total_setelah_pembulatan,
                -- Selisih pembulatan
                CAST(
                    (ROUND((txdo.jumlah_harga + 
                            txdo.jumlah_harga * 0.11 + 
                            txdo.jumlah_harga * (mt.pbbkb / 100)) / 1000) * 1000) -
                    (txdo.jumlah_harga + 
                     txdo.jumlah_harga * 0.11 + 
                     txdo.jumlah_harga * (mt.pbbkb / 100))
                    AS DECIMAL(20,2)
                ) AS jumlah_pembulatan
            FROM tx_do txdo
            INNER JOIN tx_sp3m txsp3m ON txdo.sp3m_id = txsp3m.sp3m_id
            INNER JOIN ms_kantor_sar mks ON mks.kantor_sar_id = txsp3m.kantor_sar_id
            INNER JOIN ms_harga_bekal mhb ON mhb.harga_bekal_id = txdo.harga_bekal_id
            INNER JOIN tx_alpal ta ON ta.alpal_id = txsp3m.alpal_id
            INNER JOIN ms_tbbm mt ON mt.tbbm_id = ta.tbbm_id
            WHERE txdo.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_delivery_order_with_calculations");
    }
};
