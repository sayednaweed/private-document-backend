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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->boolean('view');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->boolean('add');
            $table->unsignedBigInteger('role')->nullable();
            $table->foreign('role')->references('id')->on('roles')
                ->onUpdate('cascade')
                ->onDelete('set null');;
            $table->string('permission');
            $table->foreign('permission')->references('name')->on('permissions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index(["permission", "role"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
