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
        Schema::create('program_session_moderators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['program_session_id', 'participant_id'], 'unique_session_moderator');
            
            // Indexes
            $table->index('program_session_id');
            $table->index('participant_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_session_moderators');
    }
};