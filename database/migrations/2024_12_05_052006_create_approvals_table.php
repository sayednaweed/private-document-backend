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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string("request_comment")->nullable();
            $table->string("request_date");
            $table->string("feedback_comment")->nullable();
            $table->string("feedback_date")->nullable();
            $table->boolean("approved")->nullable();
            $table->unsignedBigInteger('approvable_id');
            $table->string('approvable_type');
            $table->unsignedBigInteger('request_type_id');
            $table->foreign('request_type_id')->references('id')->on('request_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->index(
                [
                    'approvable_id',
                    'approvable_type',
                    'request_type_id',
                    'user_id'
                ],
                'approvable_approvable_type_request_type_user_idx'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
