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
        Schema::table('ms_tbbm', function (Blueprint $table) {
            $table->dropColumn('ship_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_tbbm', function (Blueprint $table) {
            $table->char('ship_to', 6)->after('pbbkb');
        });
    }
};
