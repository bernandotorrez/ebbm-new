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
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->string('kode_alut', 3)->index('idx_kode_alut')->nullable()->after('alpal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->dropColumn('kode_alut');
        });
    }
};
