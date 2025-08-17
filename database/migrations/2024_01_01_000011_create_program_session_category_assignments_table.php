<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_session_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_session_id')
                  ->constrained('program_sessions')
                  ->onDelete('cascade')
                  ->name('fk_psca_session_id');
            $table->foreignId('program_session_category_id')
                  ->constrained('program_session_categories')
                  ->onDelete('cascade')
                  ->name('fk_psca_category_id');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['program_session_id', 'program_session_category_id'], 'unique_session_category');
            
            // Indexes
            $table->index('program_session_id', 'idx_psca_session_id');
            $table->index('program_session_category_id', 'idx_psca_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_session_category_assignments');
    }
};