<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->enum('is_active', ['0', '1'])->default('1')->after('deleted_at');
        });
        
        // Set existing records to active
        DB::table('tx_alpal')->update(['is_active' => '1']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_alpal', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
