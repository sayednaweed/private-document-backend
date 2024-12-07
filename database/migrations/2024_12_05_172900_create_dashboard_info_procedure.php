<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDashboardInfoProcedure extends Migration
{
    public function up()
    {
          $procedure = "
        CREATE PROCEDURE GetDashboardData(IN locale VARCHAR(10))
        BEGIN

           SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, st.name) AS status_name,
                COUNT(d.id) AS document_count
            FROM documents d
            JOIN statuses st ON d.status_id = st.id
            LEFT JOIN translates t ON t.translable_id = st.id
            AND t.translable_type = 'App\\\\Models\\\\Status'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            GROUP BY st.id, status_name;

            -- Return document type percentages
            SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                COUNT(d.id) AS document_count,
                IF(COUNT(d.id) > 0, ROUND((COUNT(d.id) / (SELECT COUNT(d.id) FROM documents d JOIN document_types dt ON d.document_type_id = dt.id)) * 100, 2), 0) AS percentage
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN translates t ON t.translable_id = dt.id
            AND t.translable_type = 'App\\\\Models\\\\DocumentType'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            GROUP BY dt.id, dt.name, t.value;


            -- Return document type counts for the last 1 year months

   SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                COUNT(d.id) AS document_count,
                MONTHNAME(d.created_at) AS month_name,
                MONTH(d.created_at) AS month,
                YEAR(d.created_at) AS year
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN translates t ON t.translable_id = dt.id
            AND t.translable_type = 'App\\\\Models\\\\DocumentType'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
             AND YEAR(d.created_at) = YEAR(CURDATE())
        	 GROUP BY dt.id,month, dt.name, t.value;



            -- Return document counts for the last 6 months
            SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                COUNT(d.id) AS document_count
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN translates t ON t.translable_id = dt.id
            AND t.translable_type = 'App\\\\Models\\\\DocumentType'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            WHERE d.created_at >= CURDATE() - INTERVAL 6 MONTH
            GROUP BY dt.id, dt.name, t.value;

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

        END
        ";

        // Execute the procedure creation using unprepared method
        DB::unprepared($procedure);
       }

    public function down()
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS GetDashboardData;
          
        ');
    }
}
