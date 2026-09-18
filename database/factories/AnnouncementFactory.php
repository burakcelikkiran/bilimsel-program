<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'program_session_id' => null,
            'published_at' => now(),
            'push_sent_at' => null,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }
}
