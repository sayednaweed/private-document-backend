<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trigger to prevent inserting a second user with role_id = 4
        DB::statement('
            CREATE TRIGGER prevent_multiple_role_id_4
            BEFORE INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role_id = 4 AND (SELECT COUNT(*) FROM users WHERE role_id = 4) >= 1 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Only one user can have role_id of 4.";
                END IF;
            END;
        ');

        // Trigger to prevent updating to role_id = 4 if another user already has it
        DB::statement('
            CREATE TRIGGER prevent_update_role_id_4
            BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role_id = 4 AND (SELECT COUNT(*) FROM users WHERE role_id = 4 AND id != NEW.id) >= 1 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Only one user can have role_id of 4.";
                END IF;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS prevent_multiple_role_id_4;');
        DB::statement('DROP TRIGGER IF EXISTS prevent_update_role_id_4;');
    }
};
