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
        Schema::table('tx_pemakaian', function (Blueprint $table) {
            $table->string('data_kegiatan', 50)->nullable()->after('tanggal_pakai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_pemakaian', function (Blueprint $table) {
            $table->dropColumn('data_kegiatan');
        });
    }
};
