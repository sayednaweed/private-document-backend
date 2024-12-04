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
        Schema::create('audits', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID column
            $table->string('user_type')->nullable(); // Class name of the user (e.g., App\Models\User)
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user who performed the action
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->string('event'); // Type of event (created, updated, deleted)
            $table->morphs('auditable');
            $table->json('old_values')->nullable(); // Old values of the model (for update events)
            $table->json('new_values')->nullable(); // New values of the model (for update events)
            $table->text('url')->nullable(); // The URL where the action took place
            $table->string('ip_address', 45)->nullable(); // IP address of the user performing the action
            $table->string('user_agent', 1023)->nullable(); // User agent string of the user's browser/device
            $table->string('tags')->nullable(); // Optional tags for the audit
            $table->timestamps(); // Created and updated timestamps

            // Indexes to speed up queries based on frequently used fields
            $table->index(['auditable_type', 'auditable_id'], 'cus_audits_auditable_type_auditable_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_audits');
    }
};
