<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all 'kanwil' values to 'kansar' in the users table
        DB::statement("UPDATE ms_user SET level = 'kansar' WHERE level = 'kanwil'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update all 'kansar' values back to 'kanwil' in the users table
        DB::statement("UPDATE ms_user SET level = 'kanwil' WHERE level = 'kansar'");
    }
};
