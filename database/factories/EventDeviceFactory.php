<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDevice>
 */
class EventDeviceFactory extends Factory
{
    protected $model = EventDevice::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'token' => fake()->unique()->sha256(),
            'platform' => fake()->randomElement(['android', 'ios']),
            'app' => 'tpk2026',
            'last_seen_at' => now(),
        ];
    }
}
