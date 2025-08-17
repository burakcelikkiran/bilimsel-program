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
        Schema::table('events', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('events', 'timezone')) {
                $table->string('timezone', 50)->nullable()->after('end_date');
            }
            
            if (!Schema::hasColumn('events', 'venue_address')) {
                $table->text('venue_address')->nullable()->after('location');
            }
            
            // Diğer eksik olabilecek kolonlar
            if (!Schema::hasColumn('events', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('venue_address');
            }
            
            if (!Schema::hasColumn('events', 'contact_phone')) {
                $table->string('contact_phone', 20)->nullable()->after('contact_email');
            }
            
            if (!Schema::hasColumn('events', 'website_url')) {
                $table->string('website_url')->nullable()->after('contact_phone');
            }
            
            if (!Schema::hasColumn('events', 'registration_enabled')) {
                $table->boolean('registration_enabled')->default(false)->after('is_published');
            }
            
            if (!Schema::hasColumn('events', 'max_attendees')) {
                $table->integer('max_attendees')->nullable()->after('registration_enabled');
            }
            
            if (!Schema::hasColumn('events', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('organization_id')->constrained()->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columns = ['timezone', 'venue_address', 'contact_email', 'contact_phone', 'website_url', 'registration_enabled', 'max_attendees', 'user_id'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};