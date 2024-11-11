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
        Schema::create('column_translates', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('column_name');
            $table->unsignedBigInteger('translable_id');
            $table->string('translable_type');
            $table->string('language_name');
            $table->foreign('language_name')->references('name')->on('languages')->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index(['translable_id', "language_name", 'translable_type'], 'idx_translable_id_lang_name_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('column_translates');
    }
};
