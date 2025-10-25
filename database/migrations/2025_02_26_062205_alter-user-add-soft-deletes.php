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
        // The soft deletes functionality is already included in the main users table migration,
        // so nothing to do here
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback since we're not adding soft deletes in this migration
    }
};
