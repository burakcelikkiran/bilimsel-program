<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_factory_creates_valid_record(): void
    {
        $organization = Organization::factory()->create();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => $organization->name,
        ]);
    }

    public function test_participant_belongs_to_organization(): void
    {
        $organization = Organization::factory()->create();
        $participant = Participant::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($participant->organization->is($organization));
    }

    public function test_event_factory_published_state(): void
    {
        $event = Event::factory()->published()->create();

        $this->assertTrue($event->is_published);
    }

    public function test_program_session_factory_creates_valid_record(): void
    {
        $session = \App\Models\ProgramSession::factory()->create();

        $this->assertDatabaseHas('program_sessions', [
            'id' => $session->id,
            'title' => $session->title,
        ]);
    }
}
