<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ms_user', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('kantor_sar_id')->nullable();
            $table->unsignedBigInteger('alpal_id')->nullable();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->enum('level', ['admin', 'kanpus', 'kansar', 'abk'])->default('abk');
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
        });

        // Insert default admin user
        DB::table('ms_user')->insert([
            'user_id' => 1,
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@basarnas.go.id',
            'password' => Hash::make('AdminEbmpBasarnas!'),
            'level' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_user');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};