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
                        d.status_id,
                        dt.name AS documentType,
                        u.name AS urgency,
                        s.name AS source,
                        d.document_date AS documentDate,
                        d.document_number AS documentNumber,
                        warida.number AS qaidWaridaNumber,
                        warida.date AS qaidWaridaDate,
                        sadira.date AS qaidSadiraDate,
                        sadira.number AS qaidSadiraNumber,
                        CAST(AES_DECRYPT(d.summary, encryption_key) AS CHAR) AS subject,
                        CAST(AES_DECRYPT(d.saved_file, encryption_key) AS CHAR) AS savedFile,
                        des.feedback_date AS feedbackDate,
                        des.deadline,
                        d.locked,
                        d.old_doc as oldDoc
                    FROM documents d
                    JOIN statuses st ON d.status_id = st.id
                    JOIN urgencies u ON d.urgency_id = u.id
                    JOIN document_types dt ON d.document_type_id = dt.id
                    JOIN sources s ON d.source_id = s.id
                    JOIN document_destinations des ON des.document_id = d.id
                    JOIN (
                        SELECT 
                            document_id, 
                            number, 
                            date 
                        FROM document_adverbs 
                            WHERE adverb_type_id = 1
                    ) warida ON d.id = warida.document_id
                    LEFT JOIN (
                        SELECT 
                            document_id, 
                            number, 
                            date 
                        FROM document_adverbs 
                            WHERE adverb_type_id = 2
                    ) sadira ON d.id = sadira.document_id
                    WHERE d.id = doc_id 
                    ORDER BY des.feedback_date
                    LIMIT 1;
                ELSE
                    SELECT 
                        d.id,
                        st.status,
                        d.status_id,
                        dt.documentType,
                        u.urgency,
                        s.source,
                        d.document_date AS documentDate,
                        d.document_number AS documentNumber,
                        warida.number AS qaidWaridaNumber,
                        warida.date AS qaidWaridaDate,
                        sadira.date AS qaidSadiraDate,
                        sadira.number AS qaidSadiraNumber,
                        CAST(AES_DECRYPT(d.summary, encryption_key) AS CHAR) AS subject,
                        CAST(AES_DECRYPT(d.saved_file, encryption_key) AS CHAR) AS savedFile,
                        des.feedback_date AS feedbackDate,
                        des.deadline,
                        d.locked,
                        d.old_doc AS oldDoc
                    FROM documents d
                    JOIN document_destinations des ON des.document_id = d.id
                    JOIN (
                        SELECT translable_id, value AS status
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Status' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) st ON d.status_id = st.translable_id
                    JOIN (
                        SELECT translable_id, value AS urgency
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Urgency' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) u ON d.urgency_id = u.translable_id
                    JOIN (
                        SELECT translable_id, value AS documentType
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\DocumentType' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) dt ON d.document_type_id = dt.translable_id
                    JOIN (
                        SELECT translable_id, value AS source
                        FROM translates
                        WHERE translable_type = 'App\\\\Models\\\\Source' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) s ON d.source_id = s.translable_id
                    JOIN (
                        SELECT 
                            document_id, 
                            number, 
                            date 
                        FROM document_adverbs 
                            WHERE adverb_type_id = 1
                    ) warida ON d.id = warida.document_id
                    LEFT JOIN (
                        SELECT 
                            document_id, 
                            number, 
                            date 
                        FROM document_adverbs 
                            WHERE adverb_type_id = 2
                    ) sadira ON d.id = sadira.document_id
                    WHERE d.id = doc_id
                    ORDER BY des.feedback_date
                    LIMIT 1;
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
