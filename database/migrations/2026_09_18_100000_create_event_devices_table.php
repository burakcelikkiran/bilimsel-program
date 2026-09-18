<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('token', 512);
            $table->string('platform', 16);
            $table->string('app', 64)->default('tpk2026');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique('token');
            $table->index(['event_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_devices');
    }
};
