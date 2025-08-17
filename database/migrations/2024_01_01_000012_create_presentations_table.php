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
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_session_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('presentation_type', ['keynote', 'oral', 'case_presentation', 'symposium'])->default('oral');
            $table->foreignId('sponsor_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('program_session_id');
            $table->index('sponsor_id');
            $table->index('sort_order');
            $table->index('presentation_type');
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};