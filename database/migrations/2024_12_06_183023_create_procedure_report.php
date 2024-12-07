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
        DB::unprepared("
            -- 1. Document Type Percentages Procedure
            CREATE PROCEDURE ReportSelectionContain(IN locale VARCHAR(10))
            BEGIN
              

                
                SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, st.name) AS status_name,
                    color,
                    st.id
                  
                FROM statuses st
                LEFT JOIN translates t ON t.translable_id = ar.id
                AND t.translable_type = 'App\\\\Models\\\\Status'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci;


                  SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, src.name) AS source_name,
                    src.id
                  
                FROM sources src
                LEFT JOIN translates t ON t.translable_id = src.id
                AND t.translable_type = 'App\\\\Models\\\\Source'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci;

                
             

                  SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, ur.name) AS urgency_name,
                    ur.id
                  
                FROM urgencies ur
                LEFT JOIN translates t ON t.translable_id = ur.id
                AND t.translable_type = 'App\\\\Models\\\\Urgency'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci;


                   SELECT 
                    IF(locale != 'en' AND t.value IS NOT NULL, t.value, dt.name) AS document_type_name,
                    dt.id
                  
                FROM document_types dt
                LEFT JOIN translates t ON t.translable_id = dt.id
                AND t.translable_type = 'App\\\\Models\\\\DocumentType'
                AND t.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci;

SELECT DISTINCT 
    IF(
        locale != 'en' AND t_dest.value IS NOT NULL, 
        t_dest.value, 
        des.name
    ) AS destination_name,
    
    IF(
        locale != 'en' AND t_destType.value IS NOT NULL, 
        t_destType.value, 
        desT.name
    ) AS destination_type_name,
    
    des.id
FROM 
    destinations des
JOIN 
    destination_types desT ON des.destination_type_id = desT.id
LEFT JOIN 
    translates t_dest ON t_dest.translable_id = des.id
    AND t_dest.translable_type = 'App\\\\Models\\\\Destination'
    AND t_dest.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci
LEFT JOIN 
    translates t_destType ON t_destType.translable_id = desT.id
    AND t_destType.translable_type = 'App\\\\\Models\\\\DestinationType'
    AND t_destType.language_name COLLATE utf8mb4_unicode_ci = locale COLLATE utf8mb4_unicode_ci;


            END;

        ");
    }

    public function down()
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS ReportSelectionContain;
    
        ');
    }
};
