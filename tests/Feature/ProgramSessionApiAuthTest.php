<?php

namespace Tests\Feature;

use App\Models\EventDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgramSessionApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_timeline_api(): void
    {
        $this->getJson('/api/v1/program-sessions/timeline-data')->assertUnauthorized();
        $this->postJson('/api/v1/program-sessions/bulk-update', ['sessions' => []])->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_timeline_data_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $eventDay = EventDay::factory()->create();

        $this->getJson('/api/v1/program-sessions/timeline-data?event_day_id='.$eventDay->id)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
