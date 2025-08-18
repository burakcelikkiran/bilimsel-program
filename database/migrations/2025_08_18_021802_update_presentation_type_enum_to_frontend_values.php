<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Step 1: Add old ENUM values back
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'poster', 'panel', 'workshop', 'case_presentation', 'symposium') NOT NULL DEFAULT 'oral'");
        
        // Step 2: Revert data back to original values
        DB::statement("UPDATE presentations SET presentation_type = 'case_presentation' WHERE presentation_type = 'poster'");
        DB::statement("UPDATE presentations SET presentation_type = 'symposium' WHERE presentation_type = 'panel'");
        
        // Step 3: Remove new ENUM values, keeping only the original ones
        DB::statement("ALTER TABLE presentations MODIFY presentation_type ENUM('keynote', 'oral', 'case_presentation', 'symposium') NOT NULL DEFAULT 'oral'");
    }
};