<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
 */
class SponsorFactory extends Factory
{
    protected $model = Sponsor::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'sponsor_level' => fake()->randomElement(['platinum', 'gold', 'silver', 'bronze']),
            'website' => fake()->optional()->url(),
            'contact_email' => fake()->optional()->companyEmail(),
            'is_active' => true,
        ];
    }
}
