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
        // The kantor_sar_id column is already included in the main users table migration along with its index,
        // but foreign key constraint is added separately in the foreign keys migration, so nothing to do here
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback since we're not adding the column in this migration
    }
};
