<?php

namespace Database\Seeders;

use App\Models\EventDay;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏛️ Creating venues...');

        $venueTemplates = [
            // Large Congress Venues (4+ days events)
            'large' => [
                [
                    'name' => 'ana-salon',
                    'display_name' => 'Prof. Dr. İhsan Doğramacı Ana Salon',
                    'capacity' => 800,
                    'color' => '#3B82F6',
                ],
                [
                    'name' => 'salon-a',
                    'display_name' => 'Salon A - Neonatoloji',
                    'capacity' => 300,
                    'color' => '#10B981',
                ],
                [
                    'name' => 'salon-b',
                    'display_name' => 'Salon B - Enfeksiyon',
                    'capacity' => 250,
                    'color' => '#F59E0B',
                ],
                [
                    'name' => 'salon-c',
                    'display_name' => 'Salon C - Endokrinoloji',
                    'capacity' => 200,
                    'color' => '#EF4444',
                ],
                [
                    'name' => 'poster-alani',
                    'display_name' => 'Poster Alanı',
                    'capacity' => 150,
                    'color' => '#8B5CF6',
                ],
            ],
            // Medium Events (2-3 days)
            'medium' => [
                [
                    'name' => 'ana-salon',
                    'display_name' => 'Ana Salon',
                    'capacity' => 400,
                    'color' => '#3B82F6',
                ],
                [
                    'name' => 'yan-salon',
                    'display_name' => 'Yan Salon',
                    'capacity' => 150,
                    'color' => '#10B981',
                ],
                [
                    'name' => 'workshop-alani',
                    'display_name' => 'Workshop Alanı',
                    'capacity' => 80,
                    'color' => '#F59E0B',
                ],
            ],
            // Small Events (1 day)
            'small' => [
                [
                    'name' => 'konferans-salonu',
                    'display_name' => 'Konferans Salonu',
                    'capacity' => 200,
                    'color' => '#3B82F6',
                ],
                [
                    'name' => 'toplanti-odasi',
                    'display_name' => 'Toplantı Odası',
                    'capacity' => 50,
                    'color' => '#10B981',
                ],
            ],
        ];

        $totalVenues = 0;

        foreach (EventDay::with('event')->get() as $eventDay) {
            $event = $eventDay->event;
            $totalDays = $event->eventDays()->count();
            
            // Determine venue template based on event duration
            $template = $totalDays >= 4 ? 'large' : ($totalDays >= 2 ? 'medium' : 'small');
            $venues = $venueTemplates[$template];

            $sortOrder = 1;
            foreach ($venues as $venueData) {
                Venue::create([
                    'event_day_id' => $eventDay->id,
                    'name' => $venueData['name'],
                    'display_name' => $venueData['display_name'],
                    'capacity' => $venueData['capacity'],
                    'color' => $venueData['color'],
                    'sort_order' => $sortOrder,
                ]);

                $totalVenues++;
                $sortOrder++;
            }
        }

        $this->command->info("✅ Created {$totalVenues} venues across all event days");
    }
}