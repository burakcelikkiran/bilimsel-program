<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventDay;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📆 Creating event days...');

        $events = Event::all();
        $totalDays = 0;

        foreach ($events as $event) {
            $startDate = Carbon::parse($event->start_date);
            $endDate = Carbon::parse($event->end_date);
            $dayCount = $startDate->diffInDays($endDate) + 1;

            $sortOrder = 1;
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $displayName = $this->generateDisplayName($event, $date, $sortOrder, $dayCount);
                
                EventDay::create([
                    'event_id' => $event->id,
                    'date' => $date->toDateString(),
                    'display_name' => $displayName,
                    'sort_order' => $sortOrder,
                ]);

                $totalDays++;
                $sortOrder++;
            }
        }

        $this->command->info("✅ Created {$totalDays} event days for " . $events->count() . " events");
    }

    /**
     * Generate display name for event day
     */
    private function generateDisplayName(Event $event, Carbon $date, int $dayNumber, int $totalDays): string
    {
        $dayName = $date->locale('tr')->dayName;
        $formattedDate = $date->format('d M Y');

        // Single day events
        if ($totalDays === 1) {
            return "{$formattedDate} {$dayName}";
        }

        // Multi-day events
        $specialDayNames = [
            1 => 'Açılış Günü',
            $totalDays => 'Kapanış Günü',
        ];

        if (isset($specialDayNames[$dayNumber])) {
            return "{$specialDayNames[$dayNumber]} - {$formattedDate} {$dayName}";
        }

        return "{$dayNumber}. Gün - {$formattedDate} {$dayName}";
    }
}