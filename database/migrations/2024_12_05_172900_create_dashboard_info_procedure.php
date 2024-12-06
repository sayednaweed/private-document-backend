<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDashboardInfoProcedure extends Migration
{
    public function up()
    {
        DB::unprepared("
            -- 1. Document Type Percentages Procedure
            CREATE PROCEDURE GetDocumentTypePercentages(IN locale VARCHAR(10))
            BEGIN
                DECLARE totalCount INT;

                -- Calculate total document count
                SELECT COUNT(d.id) INTO totalCount
                FROM documents d
                JOIN document_types dt ON d.document_type_id = dt.id;

                -- Return document type percentages
                SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                    COUNT(d.id) AS document_count,
                    IF(totalCount > 0, ROUND((COUNT(d.id) / totalCount) * 100, 2), 0) AS percentage
                FROM documents d
                JOIN document_types dt ON d.document_type_id = dt.id
                LEFT JOIN translates t ON t.translable_id = dt.id
                AND t.translable_type = 'App\\\\Models\\\\DocumentType'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
                GROUP BY dt.id, dt.name, t.value;
            END;

            -- 2. Document Count for the Last 6 Months Procedure
            CREATE PROCEDURE GetDocumentCountLastSixMonths(IN locale VARCHAR(10))
            BEGIN
                DECLARE startDate DATE;
                SET startDate = CURDATE() - INTERVAL 6 MONTH;

                -- Return document counts for the last 6 months
                SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                    COUNT(d.id) AS document_count
                FROM documents d
                JOIN document_types dt ON d.document_type_id = dt.id
                LEFT JOIN translates t ON t.translable_id = dt.id
                AND t.translable_type = 'App\\\\Models\\\\DocumentType'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
                WHERE d.created_at >= startDate
                GROUP BY dt.id, dt.name, t.value;
            END;

            -- 3. Document Urgency Counts Procedure
            CREATE PROCEDURE GetDocumentUrgencyCounts(IN locale VARCHAR(10))
            BEGIN
                -- Return document counts by urgency
                SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, u.name) AS urgency_name,
                    COUNT(d.id) AS document_count
                FROM documents d
                JOIN urgencies u ON d.urgency_id = u.id
                LEFT JOIN translates t ON t.translable_id = u.id
                AND t.translable_type = 'App\\\\Models\\\\Urgency'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
                GROUP BY u.id, u.name, t.value;
            END;

            -- 4. Monthly Document Counts for the Current Year Procedure
            CREATE PROCEDURE GetMonthlyDocumentCounts(IN locale VARCHAR(10))
            BEGIN
                -- Return monthly document counts for the current year
                SELECT 
                    MONTHNAME(d.created_at) AS month_name,
                    MONTH(d.created_at) AS month,
                    YEAR(d.created_at) AS year,
                    COUNT(d.id) AS document_count
                FROM documents d
                WHERE YEAR(d.created_at) = YEAR(CURDATE())
                GROUP BY month_name, month, year
                ORDER BY month;
            END;
        ");
    }

    public function down()
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS GetDocumentTypePercentages;
            DROP PROCEDURE IF EXISTS GetDocumentCountLastSixMonths;
            DROP PROCEDURE IF EXISTS GetDocumentUrgencyCounts;
            DROP PROCEDURE IF EXISTS GetMonthlyDocumentCounts;
        ');
    }
}
