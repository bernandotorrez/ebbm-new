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
        Schema::create('ms_kemasan', function (Blueprint $table) {
            $table->bigIncrements('kemasan_id');
            $table->unsignedBigInteger('pack_id')->nullable();
            $table->unsignedInteger('kemasan_liter')->default(0);
            $table->string('kemasan_pack', 50);
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            
            // Add indexes
            $table->index('kemasan_liter', 'idx_kemasan_liter');
            $table->index('kemasan_pack', 'idx_kemasan_pack');
            
            // Foreign keys
            $table->foreign('pack_id')->references('pack_id')->on('ms_pack')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_kemasan');
    }
};