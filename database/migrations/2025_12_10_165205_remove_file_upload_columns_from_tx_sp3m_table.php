<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus kolom file_upload_sp3m dan file_upload_kelengkapan_sp3m
     * karena sekarang menggunakan tabel tx_sp3m_lampiran
     */
    public function up(): void
    {
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->dropColumn(['file_upload_sp3m', 'file_upload_kelengkapan_sp3m']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->text('file_upload_sp3m')->after('jumlah_harga');
            $table->text('file_upload_kelengkapan_sp3m')->nullable()->after('file_upload_sp3m');
        });
    }
};
