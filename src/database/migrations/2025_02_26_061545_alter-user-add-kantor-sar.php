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
            $table->unsignedBigInteger('kantor_sar_id')->nullable()->index('idx_kantor_sar_id_user');
            $table->foreign('kantor_sar_id')
            ->references('kantor_sar_id')
            ->on('kantor_sars')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kantor_sar_id'); // Hapus kolom jika rollback
        });
    }
};
