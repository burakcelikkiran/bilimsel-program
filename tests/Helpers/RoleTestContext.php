<?php

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\User;
use App\Models\Venue;

function attachUserToOrganization(User $user, Organization $organization, string $pivotRole = 'editor'): void
{
    $user->organizations()->syncWithoutDetaching([
        $organization->id => ['role' => $pivotRole],
    ]);
}

/**
 * @return array{organization: Organization, user: User}
 */
function editorContext(?Organization $organization = null): array
{
    $organization ??= Organization::factory()->create();
    $user = User::factory()->editor()->create();
    attachUserToOrganization($user, $organization, 'editor');

    return compact('organization', 'user');
}

/**
 * @return array{organization: Organization, user: User}
 */
function organizerContext(?Organization $organization = null): array
{
    $organization ??= Organization::factory()->create();
    $user = User::factory()->organizer()->create();
    attachUserToOrganization($user, $organization, 'organizer');

    return compact('organization', 'user');
}

/**
 * @return array{
 *     organization: Organization,
 *     user: User,
 *     event: Event,
 *     eventDay: EventDay,
 *     venue: Venue,
 *     programSession: ProgramSession,
 *     presentation: Presentation,
 *     participant: Participant
 * }
 */
function fullEventProgram(?Organization $organization = null, ?User $user = null): array
{
    $hierarchy = programHierarchy($organization, $user);

    $participant = Participant::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
    ]);

    $presentation = Presentation::factory()->create([
        'program_session_id' => $hierarchy['programSession']->id,
        'title' => 'Test Sunumu',
        'start_time' => '10:15',
        'end_time' => '10:45',
    ]);

    return [
        ...$hierarchy,
        'participant' => $participant,
        'presentation' => $presentation,
    ];
}
