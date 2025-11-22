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
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->date('tanggal_sp3m')->nullable()->after('nomor_sp3m');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_sp3m', function (Blueprint $table) {
            $table->dropColumn('tanggal_sp3m');
        });
    }
};
