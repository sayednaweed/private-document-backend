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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // App name or client reference
            $table->string('directorate'); // directorate  or client reference
            $table->string('ip_address'); //  client ip address
            $table->string('key')->unique(); // API key
            $table->text('hashed_key'); // Hashed key for security
            $table->boolean('is_active')->default(true); // Status of the key
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
