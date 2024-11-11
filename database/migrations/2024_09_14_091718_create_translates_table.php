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
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedBigInteger('translable_id');
            $table->string('translable_type');
            $table->string('language_name');
            $table->foreign('language_name')->references('name')->on('languages')->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index(['translable_id', "language_name", 'translable_type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
