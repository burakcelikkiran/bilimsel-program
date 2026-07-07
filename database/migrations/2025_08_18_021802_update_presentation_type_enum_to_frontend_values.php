<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Updates presentation_type ENUM values to match frontend expectations:
     * - case_presentation -> poster
     * - symposium -> panel
     * - Adds workshop option
     */
    public function up(): void
    {
        if (! Schema::hasTable('presentations')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql') {
            $hasLegacyTypes = DB::table('presentations')
                ->whereIn('presentation_type', ['case_presentation', 'symposium'])
                ->exists();

            if (! $hasLegacyTypes) {
                return;
            }

            DB::table('presentations')
                ->where('presentation_type', 'case_presentation')
                ->update(['presentation_type' => 'poster']);
            DB::table('presentations')
                ->where('presentation_type', 'symposium')
                ->update(['presentation_type' => 'panel']);

            return;
        }

        $columnType = DB::select("SHOW COLUMNS FROM presentations WHERE Field = 'presentation_type'")[0]->Type ?? '';

        if (str_contains($columnType, 'poster') && ! str_contains($columnType, 'case_presentation')) {
            return;
        }

        // Step 1: Add new ENUM values while keeping old ones for safe migration
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'case_presentation', 'symposium', 'poster', 'panel', 'workshop') NOT NULL DEFAULT 'oral'");

        // Step 2: Update existing data to new values
        DB::statement("UPDATE presentations SET presentation_type = 'poster' WHERE presentation_type = 'case_presentation'");
        DB::statement("UPDATE presentations SET presentation_type = 'panel' WHERE presentation_type = 'symposium'");

        // Step 3: Remove old ENUM values, keeping only the new ones
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'poster', 'panel', 'workshop') NOT NULL DEFAULT 'oral'");
    }

    /**
     * Reverse the migrations.
     *
     * Reverts changes back to original database values
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            DB::table('presentations')
                ->where('presentation_type', 'poster')
                ->update(['presentation_type' => 'case_presentation']);
            DB::table('presentations')
                ->where('presentation_type', 'panel')
                ->update(['presentation_type' => 'symposium']);

            return;
        }

        // Step 1: Add old ENUM values back
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'poster', 'panel', 'workshop', 'case_presentation', 'symposium') NOT NULL DEFAULT 'oral'");

        // Step 2: Revert data back to original values
        DB::statement("UPDATE presentations SET presentation_type = 'case_presentation' WHERE presentation_type = 'poster'");
        DB::statement("UPDATE presentations SET presentation_type = 'symposium' WHERE presentation_type = 'panel'");

        // Step 3: Remove new ENUM values, keeping only the original ones
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'case_presentation', 'symposium') NOT NULL DEFAULT 'oral'");
    }
};
