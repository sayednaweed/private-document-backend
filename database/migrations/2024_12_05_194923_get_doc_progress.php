<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
    CREATE PROCEDURE GetDocProgress(
        IN doc_id INT,
        IN encryption_key VARCHAR(255),
        IN lang VARCHAR(10)
    )
    BEGIN
        IF lang = 'en' THEN
        SELECT
                ddd.id,
                ddd.name AS destination,
                ddd.color,
                dd.step,
                CAST(AES_DECRYPT(dd.feedback, encryption_key) AS CHAR) AS feedback,
                dd.feedback_date AS feedbackDate,
                dd.send_date AS sendDate,
                u.username
            FROM documents doc
            JOIN document_destinations dd ON dd.document_id = doc.id
            JOIN destinations ddd ON ddd.id = dd.destination_id
            JOIN users u ON u.id = dd.reciever_user_id
            WHERE doc.id = doc_id
            UNION ALL
            SELECT
                ddnd.id,
                ddnd.name AS destination,
                ddnd.color,
                NULL AS step,
                NULL AS feedbackDate,
                NULL AS feedback,
                ddn.send_date AS sendDate,  -- Fix: Add the comma here
                u2.username
            FROM documents doc
            JOIN document_destination_no_feed_backs ddn ON ddn.document_id = doc.id
            JOIN destinations ddnd ON ddnd.id = ddn.destination_id
            LEFT JOIN users u2 ON u2.id = ddn.reciever_user_id
            WHERE doc.id = doc_id
            GROUP BY id;
        ELSE
        -- First SELECT statement (with feedback)
    SELECT 
        ddd.id,
        dt.destination,
        ddd.color,
        dd.step,
        CAST(AES_DECRYPT(dd.feedback, encryption_key) AS CHAR) AS feedback,
        dd.feedback_date AS feedbackDate,
        dd.send_date AS sendDate,
        u.username
    FROM documents doc
    JOIN document_destinations dd ON dd.document_id = doc.id
    JOIN destinations ddd ON ddd.id = dd.destination_id
    JOIN (
        SELECT translable_id, value AS destination
        FROM translates
        WHERE translable_type = 'App\\\\Models\\\\Destination' 
        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
        GROUP BY translable_id
    ) dt ON ddd.id = dt.translable_id
    JOIN users u ON u.id = dd.reciever_user_id
    WHERE doc.id = doc_id

    UNION ALL

    -- Second SELECT statement (without feedback)
    SELECT 
        ddnd.id,
        dt.destination,
        ddnd.color,
        NULL AS step,                -- No step for document_destination_no_feed_backs
        NULL AS feedback,            -- No feedback for document_destination_no_feed_backs
        NULL AS feedbackDate,        -- No feedback date for document_destination_no_feed_backs
        ddn.send_date AS sendDate,   -- Send date from document_destination_no_feed_backs
        u2.username                  -- Username from document_destination_no_feed_backs
    FROM documents doc
    JOIN document_destination_no_feed_backs ddn ON ddn.document_id = doc.id
    JOIN destinations ddnd ON ddnd.id = ddn.destination_id
    JOIN (
        SELECT translable_id, value AS destination
        FROM translates
        WHERE translable_type = 'App\\\\Models\\\\Destination' 
        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
        GROUP BY translable_id
    ) dt ON ddnd.id = dt.translable_id
    LEFT JOIN users u2 ON u2.id = ddn.reciever_user_id
    WHERE doc.id = doc_id
    GROUP BY id;
        END IF;
    END
");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetDocProgress');
    }
};
