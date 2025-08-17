<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OrganizationSeeder::class,
            EventSeeder::class,
            EventDaySeeder::class,
            VenueSeeder::class,
            ParticipantSeeder::class,
            SponsorSeeder::class,
            ProgramSessionCategorySeeder::class,
            ProgramSessionSeeder::class,
            PresentationSeeder::class,
        ]);
        
        $this->command->info('✅ All seeders completed successfully!');
        $this->command->info('📊 Database populated with realistic test data');
        $this->command->newLine();
        $this->command->info('🔑 Admin Login:');
        $this->command->info('   Email: admin@example.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
        $this->command->info('🏢 Sample Organizations:');
        $this->command->info('   - Türk Pediatri Derneği');
        $this->command->info('   - Türk Neonatoloji Derneği'); 
        $this->command->info('   - Pediatrik Endokrinoloji Derneği');
    }
}