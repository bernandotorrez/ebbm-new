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
        Schema::table('ms_user', function (Blueprint $table) {
            // Add username column if it doesn't exist
            if (!Schema::hasColumn('ms_user', 'username')) {
                $table->string('username')->unique()->after('name');
            }
        });
        
        // Copy the email value to username for existing users after column is created
        if (Schema::hasColumn('ms_user', 'username')) {
            \DB::statement("UPDATE ms_user SET username = email WHERE username IS NULL OR username = '' OR username = 'NULL'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_user', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
