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
        // Update all 'crew' values to 'abk' in the users table
        DB::statement("UPDATE ms_user SET level = 'abk' WHERE level = 'crew'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update all 'abk' values back to 'crew' in the users table
        DB::statement("UPDATE ms_user SET level = 'crew' WHERE level = 'abk'");
    }
};
