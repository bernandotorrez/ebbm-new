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
            CREATE OR REPLACE VIEW view_laporan_detail_tagihan AS
            SELECT 
                txdo.tanggal_do AS tanggal_isi,
                txsp3m.nomor_sp3m,
                txdo.nomor_do,
                txdo.qty,
                COALESCE(mhb.harga, 0) AS harga_per_liter,
                CAST(txdo.qty * COALESCE(mhb.harga, 0) AS DECIMAL(20,2)) AS jumlah_harga,
                mks.kantor_sar,
                ta.alpal,
                mks.kantor_sar_id,
                txdo.do_id,
                txsp3m.sp3m_id,

                -- PPN 11%
                CAST(((txdo.qty * COALESCE(mhb.harga, 0)) * 0.11) AS DECIMAL(20,2)) AS ppn_11,

                -- PPKB
                CAST(((txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)) AS DECIMAL(20,2)) AS ppkb,

                -- Total sebelum pembulatan
                CAST(
                    (txdo.qty * COALESCE(mhb.harga, 0))
                    + ((txdo.qty * COALESCE(mhb.harga, 0)) * 0.11)
                    + ((txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100))
                    AS DECIMAL(20,2)
                ) AS total_ppn_ppkb,

                -- Total dibulatkan (ke ratusan)
                (
                    FLOOR(
                        (
                            (txdo.qty * COALESCE(mhb.harga, 0))
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)
                        ) / 100
                    ) * 100
                    +
                    CASE 
                        WHEN (
                            ((txdo.qty * COALESCE(mhb.harga, 0))
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100))
                            -
                            FLOOR(
                                (
                                    (txdo.qty * COALESCE(mhb.harga, 0))
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)
                                ) / 100
                            ) * 100
                        ) >= 500 
                        THEN 100 
                        ELSE 0 
                    END
                ) AS total_setelah_pembulatan,

                -- Nilai pembulatan (selisih)
                CAST(
                    (
                        (
                            FLOOR(
                                (
                                    (txdo.qty * COALESCE(mhb.harga, 0))
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)
                                ) / 100
                            ) * 100
                            +
                            CASE 
                                WHEN (
                                    ((txdo.qty * COALESCE(mhb.harga, 0))
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                                    + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100))
                                    -
                                    FLOOR(
                                        (
                                            (txdo.qty * COALESCE(mhb.harga, 0))
                                            + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                                            + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)
                                        ) / 100
                                    ) * 100
                                ) >= 500 
                                THEN 100 
                                ELSE 0 
                            END
                        )
                        -
                        (
                            (txdo.qty * COALESCE(mhb.harga, 0))
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * 0.11
                            + (txdo.qty * COALESCE(mhb.harga, 0)) * (mt.pbbkb / 100)
                        )
                    ) AS DECIMAL(20,2)
                ) AS jumlah_pembulatan

            FROM tx_do txdo
            INNER JOIN tx_sp3m txsp3m ON txdo.sp3m_id = txsp3m.sp3m_id
            INNER JOIN ms_kantor_sar mks ON mks.kantor_sar_id = txsp3m.kantor_sar_id
            LEFT JOIN ms_kota mk ON mk.kota_id = txdo.kota_id
            LEFT JOIN ms_harga_bekal mhb ON mhb.wilayah_id = mk.wilayah_id AND mhb.bekal_id = txdo.bekal_id
            INNER JOIN tx_alpal ta ON ta.alpal_id = txsp3m.alpal_id
            INNER JOIN ms_tbbm mt ON mt.tbbm_id = txdo.tbbm_id
            WHERE txdo.deleted_at IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_laporan_detail_tagihan");
    }
};
