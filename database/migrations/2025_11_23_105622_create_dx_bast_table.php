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
            $table->unsignedSmallInteger('qty_mulai')
                ->comment('Total Quantity dari SP3K, ini fix dan tidak bakal berubah');
            $table->unsignedSmallInteger('qty_diterima')
                ->comment('Total Quantity yang sudah diterima sampai BAST saat ini');
            $table->unsignedSmallInteger('qty_masuk')
                ->comment('Quantity yang masuk/di input dari BAST');
            $table->unsignedSmallInteger('qty_terutang')
                ->comment('Sisa Quantity Terutang berapa (qty_mulai - qty_diterima)');
            $table->decimal('jumlah_harga_mulai', 20, 2)->unsigned()
                ->comment('Total Harga dari SP3K, ini fix dan tidak bakal berubah');
            $table->decimal('jumlah_harga_diterima', 20, 2)->unsigned()
                ->comment('Total Harga yang sudah diterima sampai BAST saat ini');
            $table->decimal('jumlah_harga_masuk', 20, 2)->unsigned()
                ->comment('Harga yang masuk/di input dari BAST');
            $table->decimal('jumlah_harga_terutang', 20, 2)->unsigned()
                ->comment('Sisa Harga terutang berapa');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('bast_id')
                ->references('bast_id')
                ->on('tx_bast')
                ->noActionOnDelete()
                ->noActionOnUpdate();

            $table->foreign('pelumas_id')
                ->references('pelumas_id')
                ->on('ms_pelumas')
                ->noActionOnDelete()
                ->noActionOnUpdate();

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
