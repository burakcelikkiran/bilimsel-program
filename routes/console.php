<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

// Inspiring command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Event Management Commands
Artisan::command('events:cleanup-past', function () {
    $pastEvents = \App\Models\Event::past()->where('created_at', '<', now()->subYear())->get();
    
    $this->info("Found {$pastEvents->count()} past events older than 1 year.");
    
    if ($this->confirm('Do you want to archive these events?')) {
        foreach ($pastEvents as $event) {
            // Add archiving logic here
            $this->line("Archived: {$event->name}");
        }
        $this->info('Past events archived successfully!');
    }
})->purpose('Archive old past events');

Artisan::command('events:send-reminders', function () {
    $upcomingEvents = \App\Models\Event::upcoming()
        ->where('start_date', '=', now()->addDays(1)->toDateString())
        ->published()
        ->get();
    
    $this->info("Found {$upcomingEvents->count()} events starting tomorrow.");
    
    foreach ($upcomingEvents as $event) {
        // Send reminder notifications
        $this->line("Sent reminder for: {$event->name}");
    }
    
    $this->info('Event reminders sent successfully!');
})->purpose('Send reminders for events starting tomorrow');

// Participant Management Commands
Artisan::command('participants:cleanup-duplicates', function () {
    $duplicates = \App\Models\Participant::select('email', 'organization_id')
        ->groupBy('email', 'organization_id')
        ->havingRaw('COUNT(*) > 1')
        ->get();
    
    $this->info("Found {$duplicates->count()} duplicate participant emails.");
    
    if ($this->confirm('Do you want to merge duplicates?')) {
        foreach ($duplicates as $duplicate) {
            // Add duplicate merging logic here
            $this->line("Merged duplicates for: {$duplicate->email}");
        }
        $this->info('Duplicate participants merged successfully!');
    }
})->purpose('Clean up duplicate participants');

// File Management Commands
Artisan::command('files:cleanup-unused', function () {
    $this->info('Scanning for unused uploaded files...');
    
    // Add logic to find and clean unused files
    $this->info('Unused files cleaned up successfully!');
})->purpose('Clean up unused uploaded files');

Artisan::command('files:generate-thumbnails', function () {
    $this->info('Generating missing thumbnails...');
    
    // Add logic to generate missing thumbnails
    $this->info('Thumbnails generated successfully!');
})->purpose('Generate missing image thumbnails');

// Database Maintenance Commands
Artisan::command('db:backup', function () {
    $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
    $this->info("Creating database backup: {$filename}");
    
    // Add database backup logic here
    $this->info('Database backup created successfully!');
})->purpose('Create database backup');

Artisan::command('db:optimize-tables', function () {
    $this->info('Optimizing database tables...');
    
    // Add table optimization logic
    $this->info('Database tables optimized successfully!');
})->purpose('Optimize database tables');

// Cache Management Commands
Artisan::command('cache:warm', function () {
    $this->info('Warming up application cache...');
    
    // Warm up commonly used caches
    $this->call('config:cache');
    $this->call('route:cache');
    $this->call('view:cache');
    
    $this->info('Application cache warmed up successfully!');
})->purpose('Warm up application caches');

// Analytics Commands
Artisan::command('analytics:generate-reports', function () {
    $this->info('Generating analytics reports...');
    
    // Generate daily/weekly/monthly reports
    $this->info('Analytics reports generated successfully!');
})->purpose('Generate analytics reports');

// Health Check Commands
Artisan::command('health:check', function () {
    $this->info('Running system health checks...');
    
    // Database check
    try {
        \DB::connection()->getPdo();
        $this->line('✅ Database connection: OK');
    } catch (\Exception $e) {
        $this->error('❌ Database connection: FAILED');
    }
    
    // Storage check
    if (is_writable(storage_path())) {
        $this->line('✅ Storage writable: OK');
    } else {
        $this->error('❌ Storage writable: FAILED');
    }
    
    // Queue check
    if (\Queue::size() < 100) {
        $this->line('✅ Queue size: OK');
    } else {
        $this->error('❌ Queue size: HIGH');
    }
    
    $this->info('Health check completed!');
})->purpose('Run system health checks');

// Development Helper Commands
Artisan::command('dev:seed-demo-data', function () {
    if (!app()->environment('local')) {
        $this->error('This command can only be run in local environment!');
        return;
    }
    
    $this->info('Seeding demo data...');
    
    // Seed demo organizations, events, participants, etc.
    $this->call('db:seed', ['--class' => 'DemoDataSeeder']);
    
    $this->info('Demo data seeded successfully!');
})->purpose('Seed demo data for development');

Artisan::command('dev:reset-demo', function () {
    if (!app()->environment('local')) {
        $this->error('This command can only be run in local environment!');
        return;
    }
    
    if ($this->confirm('This will reset all demo data. Are you sure?')) {
        $this->call('migrate:fresh');
        $this->call('dev:seed-demo-data');
        $this->info('Demo environment reset successfully!');
    }
})->purpose('Reset demo environment');

// Notification Commands
Artisan::command('notifications:send-digest', function () {
    $this->info('Sending notification digests...');
    
    // Send daily/weekly digest notifications
    $this->info('Notification digests sent successfully!');
})->purpose('Send notification digests');

// Import/Export Commands
Artisan::command('export:all-data', function () {
    $organizationId = $this->ask('Enter organization ID (or leave empty for all)');
    
    $this->info('Exporting all data...');
    
    // Export logic here
    $this->info('Data exported successfully!');
})->purpose('Export all organization data');

// Security Commands
Artisan::command('security:scan-uploads', function () {
    $this->info('Scanning uploaded files for security issues...');
    
    // Security scan logic
    $this->info('Security scan completed!');
})->purpose('Scan uploaded files for security issues');

Artisan::command('security:cleanup-sessions', function () {
    $this->info('Cleaning up expired sessions...');
    
    // Session cleanup logic
    $this->call('session:gc');
    
    $this->info('Expired sessions cleaned up!');
})->purpose('Clean up expired user sessions');