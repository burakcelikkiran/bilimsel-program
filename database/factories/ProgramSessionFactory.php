<?php

namespace Database\Factories;

use App\Models\ProgramSession;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramSession>
 */
class ProgramSessionFactory extends Factory
{
    protected $model = ProgramSession::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $start = sprintf('%02d:00', $startHour);
        $end = sprintf('%02d:30', $startHour);

        return [
            'venue_id' => Venue::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_time' => $start,
            'end_time' => $end,
            'session_type' => fake()->randomElement(['main', 'satellite', 'oral_presentation', 'break', 'special']),
            'moderator_title' => 'Oturum Başkanı',
            'is_break' => false,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function breakSession(): static
    {
        return $this->state(fn () => [
            'session_type' => 'break',
            'is_break' => true,
            'start_time' => '12:00',
            'end_time' => '12:30',
        ]);
    }

    public function atTime(string $start, string $end): static
    {
        return $this->state(fn () => [
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }
}
