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
        Schema::create('program_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('session_type')->default('main');
            $table->string('moderator_title')->default('Oturum Başkanı');
            $table->foreignId('sponsor_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_break')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('venue_id');
            $table->index(['start_time', 'end_time']);
            $table->index('session_type');
            $table->index('sort_order');
            $table->index('sponsor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_sessions');
    }
};