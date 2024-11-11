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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('username');
            $table->unsignedBigInteger('email_id')->nullable();
            $table->foreign('email_id')->references('id')->on('emails')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->string('password');
            $table->string('profile')->nullable();
            $table->boolean('status');
            $table->boolean('grant_permission');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')
                ->references('id')->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->foreign('job_id')->references('id')->on('model_jobs')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->foreign('destination_id')->references('id')->on('destinations')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->index(["email_id", "job_id", "destination_id", "role_id"]);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            // Unique constraint for role_id = 1
            // $table->unique(['role_id'], 'unique_role_id_1')->where('role_id = ' . RoleEnum::super->value);
        });

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
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
