<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("SET SESSION sql_mode = 'NO_BACKSLASH_ESCAPES';");
        DB::statement("
            CREATE VIEW users_ps_view AS
            SELECT 
                u.id,
                u.username,
                u.profile,
                u.status,
                u.created_at AS createdAt,
                e.value AS email,
                c.value AS contact,
                td.destination,
                tj.job
            FROM users u
            LEFT JOIN contacts c ON u.contact_id = c.id
            LEFT JOIN emails e ON u.email_id = e.id
            LEFT JOIN (
                SELECT translable_id, value AS destination
                FROM translates
                WHERE translable_type = 'App\\Models\\Destination' 
                AND language_name = 'ps'
                GROUP BY translable_id
            ) td ON u.destination_id = td.translable_id
            LEFT JOIN (
                SELECT translable_id, value AS job
                FROM translates
                WHERE translable_type = 'App\\Models\\ModelJob' 
                AND language_name = 'ps'
                GROUP BY translable_id
            ) tj ON u.job_id = tj.translable_id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS users_fa_view');
    }
};
