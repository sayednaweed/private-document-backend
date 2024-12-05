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
        Schema::create('document_adverbs', function (Blueprint $table) {
            $table->id();
            $table->string("date");
            $table->string("number");
            $table->unsignedBigInteger('adverb_type_id');
            $table->foreign('adverb_type_id')->references('id')->on('adverb_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('documents')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index(['adverb_type_id', 'document_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_adverbs');
    }
};
