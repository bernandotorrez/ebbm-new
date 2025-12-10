<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrasi data lampiran kelengkapan SP3M yang sudah ada ke tabel tx_sp3m_lampiran
     */
    public function up(): void
    {
        // Ambil semua SP3M yang memiliki file_upload_kelengkapan_sp3m
        $sp3ms = DB::table('tx_sp3m')
            ->whereNotNull('file_upload_kelengkapan_sp3m')
            ->where('file_upload_kelengkapan_sp3m', '!=', '')
            ->get();

        foreach ($sp3ms as $sp3m) {
            // Insert ke tabel tx_sp3m_lampiran
            DB::table('tx_sp3m_lampiran')->insert([
                'sp3m_id' => $sp3m->sp3m_id,
                'nama_file' => 'Kelengkapan SP3M',
                'file_path' => $sp3m->file_upload_kelengkapan_sp3m,
                'keterangan' => 'Migrasi dari file_upload_kelengkapan_sp3m',
                'created_by' => $sp3m->created_by,
                'created_at' => $sp3m->created_at,
                'updated_at' => $sp3m->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus data yang di-migrate
        DB::table('tx_sp3m_lampiran')
            ->where('keterangan', 'Migrasi dari file_upload_kelengkapan_sp3m')
            ->delete();
    }
};
