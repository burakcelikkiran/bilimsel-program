<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'title' => fake()->optional()->randomElement(['Prof. Dr.', 'Doç. Dr.', 'Dr.']),
            'affiliation' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'bio' => fake()->optional()->paragraph(),
            'is_speaker' => fake()->boolean(70),
            'is_moderator' => fake()->boolean(30),
        ];
    }

    public function speaker(): static
    {
        return $this->state(fn () => [
            'is_speaker' => true,
            'is_moderator' => false,
        ]);
    }
}
