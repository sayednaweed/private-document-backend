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
        DB::statement('
        CREATE VIEW documents_en_view AS
        SELECT 
           d.id,
           d.document_date as documentDate,
           d.document_number as documentNumber,
           d.created_at as createdAt,
           st.name as status,
           st.color as statusColor,
           u.name as urgency,
           dt.name as type,
           s.name as source,
           dd.deadline
        FROM documents d
        LEFT JOIN statuses st ON d.status_id = st.id
        LEFT JOIN urgencies u ON d.urgency_id  = u.id
        LEFT JOIN document_types dt ON d.document_type_id  = dt.id
        LEFT JOIN sources s ON d.source_id  = s.id
        LEFT JOIN document_destinations dd ON d.id  = dd.id;
    ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS documents_en_view');
    }
};
