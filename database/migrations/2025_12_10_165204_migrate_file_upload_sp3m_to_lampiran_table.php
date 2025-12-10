<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrasi data file_upload_sp3m ke tabel tx_sp3m_lampiran
     */
    public function up(): void
    {
        // Ambil semua SP3M yang memiliki file_upload_sp3m
        $sp3ms = DB::table('tx_sp3m')
            ->whereNotNull('file_upload_sp3m')
            ->where('file_upload_sp3m', '!=', '')
            ->get();

        foreach ($sp3ms as $sp3m) {
            // Cek apakah sudah ada lampiran dengan file_path yang sama
            $exists = DB::table('tx_sp3m_lampiran')
                ->where('sp3m_id', $sp3m->sp3m_id)
                ->where('file_path', $sp3m->file_upload_sp3m)
                ->exists();

            if (!$exists) {
                // Insert ke tabel tx_sp3m_lampiran
                DB::table('tx_sp3m_lampiran')->insert([
                    'sp3m_id' => $sp3m->sp3m_id,
                    'nama_file' => 'SP3M',
                    'file_path' => $sp3m->file_upload_sp3m,
                    'keterangan' => 'Migrasi dari file_upload_sp3m',
                    'created_by' => $sp3m->created_by,
                    'created_at' => $sp3m->created_at,
                    'updated_at' => $sp3m->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus data yang di-migrate
        DB::table('tx_sp3m_lampiran')
            ->where('keterangan', 'Migrasi dari file_upload_sp3m')
            ->delete();
    }
};
