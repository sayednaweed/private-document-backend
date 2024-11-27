<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
        CREATE VIEW users_en_view AS
        SELECT 
            u.id,
            u.username,
            u.profile,
            u.status,
            u.created_at AS createdAt,
            e.value as email,
			c.value as contact,
            d.name as destination,
            j.name as job
        FROM users u
        LEFT JOIN contacts c ON u.contact_id = c.id
        LEFT JOIN emails e ON u.email_id = e.id
        LEFT JOIN model_jobs j ON u.job_id = j.id
        LEFT JOIN destinations d ON u.destination_id = d.id;
    ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS users_en_view');
    }
};
