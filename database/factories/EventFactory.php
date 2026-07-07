<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = fake()->sentence(3);
        $start = fake()->dateTimeBetween('+1 week', '+3 months');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->optional()->paragraph(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'location' => fake()->optional()->city(),
            'is_published' => false,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true]);
    }
}
