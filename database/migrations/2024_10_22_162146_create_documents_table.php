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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 64);
            $table->binary('summary');
            $table->binary('saved_file')->nullable()->comment('In Which book shell document is saved');
            $table->string('document_date');
            $table->boolean('disabled');
            $table->boolean('old');
            $table->unsignedBigInteger('document_type_id');
            $table->foreign('document_type_id')->references('id')->on('document_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('statuses')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('urgency_id');
            $table->foreign('urgency_id')->references('id')->on('urgencies')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('source_id');
            $table->foreign('source_id')->references('id')->on('sources')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('reciever_user_id');
            $table->foreign('reciever_user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->index(['document_type_id', 'source_id', 'urgency_id', 'status_id'], 'doc_type_source_urgency_status_idx');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
