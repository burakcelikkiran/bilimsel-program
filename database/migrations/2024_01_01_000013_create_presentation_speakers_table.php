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
        Schema::create('presentation_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->enum('speaker_role', ['primary', 'co_speaker', 'discussant'])->default('primary');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['presentation_id', 'participant_id'], 'unique_presentation_speaker');
            
            // Indexes
            $table->index('presentation_id');
            $table->index('participant_id');
            $table->index('speaker_role');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentation_speakers');
    }
};