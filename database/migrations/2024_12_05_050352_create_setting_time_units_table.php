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
        Schema::create('setting_time_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('time_unit_id');
            $table->foreign('time_unit_id')->references('id')->on('time_units')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedBigInteger('setting_id');
            $table->foreign('setting_id')->references('id')->on('settings')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_time_units');
    }
};
