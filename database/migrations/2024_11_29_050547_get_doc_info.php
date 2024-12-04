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
        DB::unprepared("
            CREATE PROCEDURE GetDocInfo(
                IN doc_id INT, 
                IN encryption_key VARCHAR(255),
                IN lang VARCHAR(10)
            )
            BEGIN
                IF lang = 'en' THEN
                    SELECT 
                        d.id,
                        st.name AS status,
                        dt.name AS documentType,
                        u.name AS urgency,
                        s.name AS source,
                        d.document_date AS documentDate,
                        d.document_number AS documentNumber,
                        d.qaid_warida_number AS qaidWaridaNumber,
                        d.qaid_warida_date AS qaidWaridaDate,
                        d.qaid_sadira_date AS qaidSadiraDate,
                        d.qaid_sadira_number AS qaidSadiraNumber,
                        CAST(AES_DECRYPT(d.summary, encryption_key) AS CHAR) AS subject,
                        CAST(AES_DECRYPT(d.muqam_statement, encryption_key) AS CHAR) AS muqamStatement,
                        CAST(AES_DECRYPT(d.saved_file, encryption_key) AS CHAR) AS savedFile,
                        d.disabled,
                        dd.deadline
                    FROM (SELECT * FROM documents WHERE id = doc_id) d
                    LEFT JOIN statuses st ON d.status_id = st.id
                    LEFT JOIN urgencies u ON d.urgency_id = u.id
                    LEFT JOIN document_types dt ON d.document_type_id = dt.id
                    LEFT JOIN sources s ON d.source_id = s.id
                    LEFT JOIN document_destinations dd ON d.id = dd.document_id;
                ELSE
                    SELECT 
                        d.id,
                        st.status,
                        dt.documentType,
                        u.urgency,
                        s.source,
                        d.document_date AS documentDate,
                        d.document_number AS documentNumber,
                        d.qaid_warida_number AS qaidWaridaNumber,
                        d.qaid_warida_date AS qaidWaridaDate,
                        d.qaid_sadira_date AS qaidSadiraDate,
                        d.qaid_sadira_number AS qaidSadiraNumber,
                        CAST(AES_DECRYPT(d.summary, encryption_key) AS CHAR) AS subject,
                        CAST(AES_DECRYPT(d.muqam_statement, encryption_key) AS CHAR) AS muqamStatement,
                        CAST(AES_DECRYPT(d.saved_file, encryption_key) AS CHAR) AS savedFile,
                        d.disabled,
                        dd.deadline
                    FROM (SELECT * FROM documents WHERE id = doc_id) d
                    LEFT JOIN (
                        SELECT translable_id, value AS status
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Status' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) st ON d.status_id = st.translable_id
                    LEFT JOIN (
                        SELECT translable_id, value AS urgency
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Urgency' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) u ON d.urgency_id = u.translable_id
                    LEFT JOIN (
                        SELECT translable_id, value AS documentType
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\DocumentType' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) dt ON d.document_type_id = dt.translable_id
                    LEFT JOIN (
                        SELECT translable_id, value AS source
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Source' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) s ON d.source_id = s.translable_id
                    LEFT JOIN document_destinations dd ON d.id = dd.document_id;
                END IF;
            END
        ");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetDocInfo');
    }
};
