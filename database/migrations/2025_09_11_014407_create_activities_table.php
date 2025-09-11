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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // event_created, session_created, participant_added, etc.
            $table->text('description'); // Human readable description
            $table->string('subject_type'); // Model class name (App\Models\Event)
            $table->unsignedBigInteger('subject_id'); // Model ID
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who performed the action
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade'); // Which organization
            $table->json('properties')->nullable(); // Additional data (old/new values, etc.)
            $table->timestamp('performed_at'); // When the action was performed
            $table->timestamps();

            // Indexes for better performance
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'performed_at']);
            $table->index(['organization_id', 'performed_at']);
            $table->index(['type', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
