<?php

use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use Laravel\Sanctum\Sanctum;

it('redirects guest from admin event edit', function () {
    $event = Event::factory()->create();

    test()->get(route('admin.events.edit', $event))
        ->assertRedirect(route('login'));
});

it('denies editor from updating event in other organization', function () {
    ['user' => $editor] = editorContext();
    $otherEvent = Event::factory()->create([
        'organization_id' => Organization::factory()->create()->id,
    ]);

    test()->actingAs($editor)
        ->put(route('admin.events.update', $otherEvent), [
            'organization_id' => $otherEvent->organization_id,
            'title' => 'Yetkisiz Güncelleme',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
        ])
        ->assertForbidden();
});

it('allows editor to store participant in own organization', function () {
    ['organization' => $org, 'user' => $editor] = editorContext();

    test()->actingAs($editor)
        ->post(route('admin.participants.store'), [
            'organization_id' => $org->id,
            'first_name' => 'Editor',
            'last_name' => 'Katılımcı',
            'email' => 'editor-participant@example.com',
            'is_speaker' => false,
            'is_moderator' => false,
        ])
        ->assertRedirect();

    expect(Participant::where('email', 'editor-participant@example.com')->exists())->toBeTrue();
});

it('denies editor from storing participant in other organization', function () {
    ['user' => $editor] = editorContext();
    $otherOrg = Organization::factory()->create();

    test()->actingAs($editor)
        ->post(route('admin.participants.store'), [
            'organization_id' => $otherOrg->id,
            'first_name' => 'Yetkisiz',
            'last_name' => 'Katılımcı',
            'is_speaker' => false,
            'is_moderator' => false,
        ])
        ->assertForbidden();
});

it('allows organizer to attach user to organization', function () {
    ['organization' => $org, 'user' => $organizer] = organizerContext();
    $newUser = \App\Models\User::factory()->editor()->create();

    test()->actingAs($organizer)
        ->post(route('admin.organizations.users.attach', $org), [
            'user_id' => $newUser->id,
            'role' => 'editor',
        ])
        ->assertRedirect();

    expect($newUser->organizations()->where('organizations.id', $org->id)->exists())->toBeTrue();
});

it('returns unauthorized for sanctum bulk update without token', function () {
    test()->postJson('/api/v1/program-sessions/bulk-update', [
        'sessions' => [],
    ])->assertUnauthorized();
});

it('allows sanctum user to access timeline data endpoint', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->getJson('/api/v1/program-sessions/timeline-data?event_day_id='.$hierarchy['eventDay']->id)
        ->assertOk()
        ->assertJsonStructure(['success']);
});
