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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('level', ['admin', 'kanpus', 'kanwil', 'crew'])
            ->default('crew')
            ->comment('admin = Admin | kanpus = Kantor Pusat | kanwil = Kantor Wilayah | crew = Crew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('level'); // Hapus kolom jika rollback
        });
    }
};
