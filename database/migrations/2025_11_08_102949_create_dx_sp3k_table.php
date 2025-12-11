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
        Schema::create('dx_sp3k', function (Blueprint $table) {
            $table->bigIncrements('detail_sp3k_id');
            $table->unsignedBigInteger('sp3k_id');
            $table->unsignedBigInteger('pelumas_id');
            $table->unsignedSmallInteger('qty');
            $table->decimal('harga', 20, 2)->default(0);
            $table->unsignedMediumInteger('liter')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');

            $table->foreign('sp3k_id')
                ->references('sp3k_id')
                ->on('tx_sp3k')
                ->noActionOnDelete()
                ->noActionOnUpdate();

            $table->foreign('pelumas_id')
                ->references('pelumas_id')
                ->on('ms_pelumas')
                ->noActionOnDelete()
                ->noActionOnUpdate();

            $table->index('sp3k_id', 'idx_sp3k_id');
            $table->index('pelumas_id', 'idx_pelumas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dx_sp3k');
    }
};
