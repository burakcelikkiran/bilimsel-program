<?php

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Organization;
use App\Models\ProgramSession;
use App\Models\User;
use App\Models\Venue;

/**
 * @return array{organization: Organization, user: User}
 */
function adminContext(): array
{
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['role' => 'admin']);

    return compact('organization', 'user');
}

/**
 * @return array{
 *     organization: Organization,
 *     user: User,
 *     event: Event,
 *     eventDay: EventDay,
 *     venue: Venue,
 *     programSession: ProgramSession
 * }
 */
function programHierarchy(?Organization $organization = null, ?User $user = null): array
{
    $organization ??= Organization::factory()->create();
    $user ??= User::factory()->create(['role' => 'admin']);

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $eventDay = EventDay::factory()->create([
        'event_id' => $event->id,
        'date' => $event->start_date->toDateString(),
    ]);

    $venue = Venue::factory()->create([
        'event_day_id' => $eventDay->id,
    ]);

    $programSession = ProgramSession::factory()->create([
        'venue_id' => $venue->id,
        'start_time' => '10:00',
        'end_time' => '11:00',
        'session_type' => 'main',
        'is_break' => false,
    ]);

    return compact('organization', 'user', 'event', 'eventDay', 'venue', 'programSession');
}
