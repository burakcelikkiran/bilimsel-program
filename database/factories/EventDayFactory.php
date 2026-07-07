<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDay>
 */
class EventDayFactory extends Factory
{
    protected $model = EventDay::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'display_name' => 'Gün '.fake()->numberBetween(1, 5),
            'sort_order' => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
