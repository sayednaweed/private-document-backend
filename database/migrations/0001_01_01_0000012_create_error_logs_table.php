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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->text('error_message');
            $table->text('trace');
            $table->text('error_code')->nullable();
            $table->text('exception_type')->nullable();
            $table->string("user_id");
            $table->string("username");
            $table->string("ip_address");
            $table->string("method");
            $table->string("uri");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
