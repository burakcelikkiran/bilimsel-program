<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_events_index_page_renders(): void
    {
        $organization = Organization::factory()->create();
        Event::factory()->published()->create(['organization_id' => $organization->id]);

        $response = $this->get(route('events.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Events/Index')
                ->has('events', 1)
            );
    }

    public function test_public_event_show_page_renders_for_published_event(): void
    {
        $event = Event::factory()->published()->create(['slug' => 'acik-kongre']);

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Events/Show')
                ->where('activeTab', 'overview')
            );
    }

    public function test_unpublished_event_show_page_returns_404(): void
    {
        $event = Event::factory()->create([
            'slug' => 'kapali-kongre',
            'is_published' => false,
        ]);

        $this->get(route('events.show', $event->slug))->assertNotFound();
    }
}
