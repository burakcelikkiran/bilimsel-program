<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ProgramSessionCategory;
use Illuminate\Database\Seeder;

class ProgramSessionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏷️ Creating program session categories...');

        $categoryTemplates = [
            [
                'name' => 'Ana Oturumlar',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Uydu Sempozyumları',
                'color' => '#10B981',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sözlü Bildiri Oturumları',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
            [
                'name' => 'Turkish Archives of Pediatrics',
                'color' => '#8B5CF6',
                'sort_order' => 4,
            ],
            [
                'name' => 'Genç Pediatristler',
                'color' => '#EF4444',
                'sort_order' => 5,
            ],
            [
                'name' => 'Konsültasyon Saati',
                'color' => '#06B6D4',
                'sort_order' => 6,
            ],
            [
                'name' => 'En İyi Bildiriler',
                'color' => '#F97316',
                'sort_order' => 7,
            ],
            [
                'name' => 'Poster Oturumları',
                'color' => '#84CC16',
                'sort_order' => 8,
            ],
            [
                'name' => 'Workshop',
                'color' => '#EC4899',
                'sort_order' => 9,
            ],
            [
                'name' => 'Panel Tartışması',
                'color' => '#6366F1',
                'sort_order' => 10,
            ],
        ];

        $events = Event::all();
        $totalCategories = 0;

        foreach ($events as $event) {
            // Her etkinlik için kategori sayısını belirle
            $categoryCount = $event->eventDays()->count() >= 3 ? 
                rand(6, 10) : // Büyük etkinlikler için 6-10 kategori
                rand(3, 6);   // Küçük etkinlikler için 3-6 kategori

            $selectedCategories = collect($categoryTemplates)->random($categoryCount);

            foreach ($selectedCategories as $categoryData) {
                ProgramSessionCategory::create([
                    'event_id' => $event->id,
                    'name' => $categoryData['name'],
                    'color' => $categoryData['color'],
                    'sort_order' => $categoryData['sort_order'],
                ]);

                $totalCategories++;
            }
        }

        $this->command->info("✅ Created {$totalCategories} categories across " . $events->count() . " events");
    }
}