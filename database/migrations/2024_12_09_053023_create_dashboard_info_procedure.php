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
    public function up()
    {
        $procedure = "
        CREATE PROCEDURE GetDashboardData(IN locale VARCHAR(10))
   BEGIN

           SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, st.name) AS status_name,
                COUNT(d.id) AS document_count
            FROM documents d
            Right JOIN statuses st ON d.status_id = st.id
            LEFT JOIN translates t ON t.translable_id = st.id
            AND t.translable_type = 'App\\\\Models\\\\Status'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            GROUP BY st.id, status_name;

            -- Return document type percentages
            SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                COUNT(d.id) AS document_count,
                IF(COUNT(d.id) > 0, ROUND((COUNT(d.id) / (SELECT COUNT(d.id) FROM documents d Right JOIN document_types dt ON d.document_type_id = dt.id)) * 100, 2), 0) AS percentage
            FROM documents d
            Right JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN translates t ON t.translable_id = dt.id
            AND t.translable_type = 'App\\\\Models\\\\DocumentType'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            GROUP BY dt.id, dt.name, t.value;

            -- Return document counts by urgency
            SELECT 
                IF(locale != 'en' AND t.value IS NOT NULL, t.value, u.name) AS urgency_name,
                COUNT(d.id) AS document_count
            FROM documents d
            Right JOIN urgencies u ON d.urgency_id = u.id
            LEFT JOIN translates t ON t.translable_id = u.id
            AND t.translable_type = 'App\\\\Models\\\\Urgency'
            AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
            GROUP BY u.id, u.name, t.value;

            -- Return monthly document counts for the current year
            SELECT 
    MONTHNAME(d.created_at) AS month_name,
    MONTH(d.created_at) AS month,
    COUNT(d.id) AS document_count
FROM 
    documents d
WHERE 
    YEAR(d.created_at) = YEAR(CURDATE())
GROUP BY 
    MONTH(d.created_at), MONTHNAME(d.created_at)
ORDER BY 
    month;
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
};
