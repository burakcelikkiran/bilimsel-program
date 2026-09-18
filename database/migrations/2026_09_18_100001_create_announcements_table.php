<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->foreignId('program_session_id')->nullable()->constrained('program_sessions')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('push_sent_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
