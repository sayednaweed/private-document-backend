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
        DB::statement("SET SESSION sql_mode = 'NO_BACKSLASH_ESCAPES';");
        DB::statement("
        CREATE VIEW documents_fa_view AS
        SELECT  
            d.id,
            d.document_date AS documentDate,
            d.document_number AS documentNumber,
            d.created_at AS createdAt,
            st.status,
            st.color AS statusColor, -- Assuming color is part of the status model
            u.urgency,
            dt.type,
            s.source,
            dd.deadline
        FROM documents d
        LEFT JOIN (
            SELECT translable_id, value AS status, color
            FROM translates
            JOIN statuses AS s ON s.id = translates.translable_id -- Assuming 'statuses' is the model/table for status
            WHERE translable_type = 'App\\Models\\Status' 
            AND language_name = 'fa'
            GROUP BY translable_id
        ) st ON d.status_id = st.translable_id
        LEFT JOIN ( 
            SELECT translable_id, value AS urgency 
            FROM translates
            WHERE translable_type = 'App\\Models\\Urgency' 
            AND language_name = 'fa'
            GROUP BY translable_id
        ) u ON d.urgency_id = u.translable_id
        LEFT JOIN ( 
            SELECT translable_id, value AS type 
            FROM translates
            WHERE translable_type = 'App\\Models\\DestinationType' 
            AND language_name = 'fa'
            GROUP BY translable_id
        ) dt ON d.document_type_id = dt.translable_id
        LEFT JOIN ( 
            SELECT translable_id, value AS source 
            FROM translates
            WHERE translable_type = 'App\\Models\\Source' 
            AND language_name = 'fa'
            GROUP BY translable_id
        ) s ON d.source_id = s.translable_id
        LEFT JOIN document_destinations dd ON d.id = dd.id;
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS documents_fa_view');
    }
};
