<?php

namespace Database\Factories;

use App\Models\Presentation;
use App\Models\ProgramSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Presentation>
 */
class PresentationFactory extends Factory
{
    protected $model = Presentation::class;

    public function definition(): array
    {
        return [
            'program_session_id' => ProgramSession::factory(),
            'title' => fake()->sentence(4),
            'abstract' => fake()->optional()->paragraph(),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'duration_minutes' => 30,
            'presentation_type' => 'oral',
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
