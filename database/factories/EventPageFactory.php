<?php

namespace Database\Factories;

use App\Enums\EventPageKey;
use App\Models\Event;
use App\Models\EventPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPage>
 */
class EventPageFactory extends Factory
{
    protected $model = EventPage::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'key' => fake()->randomElement(EventPageKey::cases()),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 6),
        ];
    }

    public function forKey(EventPageKey $key): static
    {
        return $this->state(fn () => [
            'key' => $key,
            'sort_order' => $key->sortOrder(),
        ]);
    }
}
