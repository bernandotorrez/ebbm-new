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
        // Update tx_bast table
        Schema::table('tx_bast', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['kantor_sar_id']);
            
            // Drop old columns
            $table->dropColumn(['kantor_sar_id', 'tahun_anggaran', 'sequence']);
            
            // Add new columns
            $table->unsignedTinyInteger('bast_ke')->after('tanggal_bast')->comment('BAST ke berapa?');
            $table->enum('sudah_diterima_semua', ['0', '1'])->default('0')->after('bast_ke')
                ->comment('Kalau 0 berarti masih terutang/outstanding, kalau 1 berarti sudah selesai BAST nya');
        });

        // Update dx_bast table
        Schema::table('dx_bast', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['qty_bast', 'sisa_qty_sp3k', 'file_upload_lampiran', 'sort']);
            
            // Add new columns
            $table->unsignedSmallInteger('qty_mulai')->after('pelumas_id')
                ->comment('Total Quantity dari SP3K, ini fix dan tidak bakal berubah');
            $table->unsignedSmallInteger('qty_diterima')->after('qty_mulai')
                ->comment('Total Quantity yang sudah diterima sampai BAST saat ini');
            $table->unsignedSmallInteger('qty_masuk')->after('qty_diterima')
                ->comment('Quantity yang masuk/di input dari BAST');
            $table->unsignedSmallInteger('qty_terutang')->after('qty_masuk')
                ->comment('Sisa Quantity Terutang berapa (qty_mulai - qty_diterima)');
            
            $table->decimal('jumlah_harga_mulai', 20, 2)->unsigned()->after('qty_terutang')
                ->comment('Total Harga dari SP3K, ini fix dan tidak bakal berubah');
            $table->decimal('jumlah_harga_diterima', 20, 2)->unsigned()->after('jumlah_harga_mulai')
                ->comment('Total Harga yang sudah diterima sampai BAST saat ini');
            $table->decimal('jumlah_harga_masuk', 20, 2)->unsigned()->after('jumlah_harga_diterima')
                ->comment('Harga yang masuk/di input dari BAST');
            $table->decimal('jumlah_harga_terutang', 20, 2)->unsigned()->after('jumlah_harga_masuk')
                ->comment('Sisa Harga terutang berapa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert tx_bast table
        Schema::table('tx_bast', function (Blueprint $table) {
            $table->dropColumn(['bast_ke', 'sudah_diterima_semua']);
            
            $table->unsignedBigInteger('kantor_sar_id')->nullable();
            $table->char('tahun_anggaran', 4)->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);
        });

        // Revert dx_bast table
        Schema::table('dx_bast', function (Blueprint $table) {
            $table->dropColumn([
                'qty_mulai', 'qty_diterima', 'qty_masuk', 'qty_terutang',
                'jumlah_harga_mulai', 'jumlah_harga_diterima', 'jumlah_harga_masuk', 'jumlah_harga_terutang'
            ]);
            
            $table->unsignedSmallInteger('qty_bast');
            $table->unsignedSmallInteger('sisa_qty_sp3k');
            $table->string('file_upload_lampiran', 255)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
        });
    }
};
