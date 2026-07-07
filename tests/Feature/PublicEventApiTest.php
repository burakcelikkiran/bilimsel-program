<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_events_are_listed_via_api(): void
    {
        $organization = Organization::factory()->create();
        Event::factory()->published()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Kongresi',
        ]);
        Event::factory()->create([
            'organization_id' => $organization->id,
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/v1/events');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_published_event_can_be_fetched_by_slug(): void
    {
        $event = Event::factory()->published()->create([
            'slug' => 'test-kongresi-2026',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->slug);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'test-kongresi-2026');
    }

    public function test_unpublished_event_returns_404_via_api(): void
    {
        $event = Event::factory()->create([
            'slug' => 'gizli-etkinlik',
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/events/'.$event->slug)->assertNotFound();
    }
}
