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
            CREATE PROCEDURE GetDocScans(
                IN doc_id INT, 
                IN lang VARCHAR(10)
            )
            BEGIN
                IF lang = 'en' THEN
                    SELECT 
                        s.id AS scanId, 
                        s.name, 
                        s.path, 
                        s.created_at AS uploadedDate, 
                        des.name AS destination, 
                        des.color,
                        u.username
                    FROM documents d
                    JOIN document_destinations dd ON dd.document_id = d.id
                    JOIN scans s ON s.document_id = d.id AND s.document_destination_id = dd.id
                    JOIN users u ON u.id = d.reciever_user_id
                    JOIN destinations des ON des.id = dd.destination_id
                    WHERE d.id = doc_id;
                ELSE
                    SELECT s.id AS scanId,
                        s.name, s.path,
                        s.created_at AS uploadedDate, 
                        dt.destination, 
                        des.color,
                        u.username
                    FROM documents d
                    JOIN document_destinations dd ON dd.document_id = d.id
                    JOIN scans s ON s.document_id = d.id AND s.document_destination_id = dd.id
                    JOIN users u ON u.id = d.reciever_user_id
                    JOIN destinations des ON des.id = dd.destination_id
                    JOIN (
                        SELECT translable_id, 
                            value AS destination 
                        FROM translates 
                        WHERE translable_type = 'App\\\\Models\\\\Destination' 
                        AND language_name COLLATE utf8mb4_unicode_ci = lang COLLATE utf8mb4_unicode_ci
                        GROUP BY translable_id
                    ) dt ON dt.translable_id = des.id
                     WHERE d.id = doc_id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetDocScans');
    }
};

// SELECT 
//     s.id AS scanId, 
//     s.name, 
//     s.path, 
//     s.created_at AS uploadedDate, 
//     des.name AS destination, 
//     des.color,
//     u.username
// FROM documents d
// JOIN document_destinations dd ON dd.document_id = d.id
// JOIN scans s ON s.document_id = d.id AND s.document_destination_id = dd.id
// JOIN users u ON u.id = d.reciever_user_id
// JOIN destinations des ON des.id = dd.destination_id
// WHERE d.id = 1