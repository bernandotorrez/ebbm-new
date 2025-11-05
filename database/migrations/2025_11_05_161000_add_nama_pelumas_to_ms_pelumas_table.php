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
        Schema::table('ms_pelumas', function (Blueprint $table) {
            $table->string('nama_pelumas', 200)->after('pelumas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_pelumas', function (Blueprint $table) {
            $table->dropColumn('nama_pelumas');
        });
    }
};