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
        Schema::table('tx_sp3k', function (Blueprint $table) {
            $table->bigInteger('alpal_id')->nullable()->after('sp3k_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_sp3k', function (Blueprint $table) {
            $table->dropColumn('alpal_id');
        });
    }
};
