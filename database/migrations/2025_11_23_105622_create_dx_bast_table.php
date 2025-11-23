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
        Schema::create('dx_bast', function (Blueprint $table) {
            $table->bigIncrements('detail_bast_id');
            $table->unsignedBigInteger('bast_id');
            $table->unsignedBigInteger('pelumas_id');
            $table->unsignedSmallInteger('qty_bast');
            $table->unsignedSmallInteger('sisa_qty_sp3k')->comment('Sisa qty dari SP3K sebelum BAST ini');
            $table->string('file_upload_lampiran', 255)->nullable()->comment('Lampiran per detail');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('bast_id')
                ->references('bast_id')
                ->on('tx_bast')
                ->onDelete('restrict');

            $table->foreign('pelumas_id')
                ->references('pelumas_id')
                ->on('ms_pelumas')
                ->onDelete('restrict');

            $table->index('bast_id', 'idx_bast_id');
            $table->index('pelumas_id', 'idx_pelumas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dx_bast');
    }
};
