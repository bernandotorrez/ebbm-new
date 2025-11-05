<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing 'kanwil' values to 'kansar'
        DB::statement("UPDATE ms_user SET level = 'kansar' WHERE level = 'kanwil'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update 'kansar' values back to 'kanwil'
        DB::statement("UPDATE ms_user SET level = 'kanwil' WHERE level = 'kansar'");
    }
};
