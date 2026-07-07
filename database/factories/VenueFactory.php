<?php

namespace Database\Factories;

use App\Models\EventDay;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Ana Salon', 'Kongre Salonu', 'Workshop Odası']).' '.fake()->numberBetween(1, 3);

        return [
            'event_day_id' => EventDay::factory(),
            'name' => $name,
            'display_name' => $name,
            'capacity' => fake()->numberBetween(50, 500),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }
}
