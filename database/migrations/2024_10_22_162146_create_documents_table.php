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
            $table->string('document_number',64);
            $table->string('summary');
            $table->string('muqam_statement')->nullable();
            $table->string('qaid_warida_number');
            $table->string('qaid_sadira_number')->nullable();
            $table->string('saved_file')->nullable()->comment('info document save in which files');
            $table->date('document_date');
            $table->date('qaid_warida_date');
            $table->date('qaid_sadira_date')->nullable();
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('types')
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
                $table->unsignedBigInteger('scan_id')->unique();
            $table->foreign('scan_id')->references('id')->on('scans')
                ->onUpdate('cascade')
                ->onDelete('no action');
                $table->unsignedBigInteger('reciever_user_id');
            $table->foreign('reciever_user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('no action');
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
