<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEventDayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_event_days_api(): void
    {
        $event = Event::factory()->create();

        $this->getJson("/api/v1/admin/events/{$event->id}/days")->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_event_days_for_accessible_event(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'editor']);
        $organization->users()->attach($user->id, ['role' => 'editor']);

        $event = Event::factory()->create(['organization_id' => $organization->id]);
        EventDay::factory()->count(2)->create(['event_id' => $event->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/admin/events/{$event->id}/days")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_cannot_list_days_for_inaccessible_event(): void
    {
        $event = Event::factory()->create();
        EventDay::factory()->create(['event_id' => $event->id]);

        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));

        $this->getJson("/api/v1/admin/events/{$event->id}/days")->assertForbidden();
    }

    public function test_authenticated_user_can_show_event_day_for_accessible_event(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'editor']);
        $organization->users()->attach($user->id, ['role' => 'editor']);

        $event = Event::factory()->create(['organization_id' => $organization->id]);
        $eventDay = EventDay::factory()->create(['event_id' => $event->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/admin/events/{$event->id}/days/{$eventDay->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $eventDay->id);
    }
}
