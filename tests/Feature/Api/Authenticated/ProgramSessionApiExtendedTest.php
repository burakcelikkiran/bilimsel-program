<?php

use Laravel\Sanctum\Sanctum;

it('returns timeline data for authenticated user', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->getJson('/api/v1/program-sessions/timeline-data?event_day_id='.$hierarchy['eventDay']->id)
        ->assertOk()
        ->assertJsonStructure(['success']);
});

it('updates session venue via sanctum api', function () {
    $hierarchy = programHierarchy();
    $targetVenue = \App\Models\Venue::factory()->create(['event_day_id' => $hierarchy['eventDay']->id]);
    Sanctum::actingAs($hierarchy['user']);

    test()->postJson("/api/v1/program-sessions/{$hierarchy['programSession']->id}/update-venue", [
        'venue_id' => $targetVenue->id,
    ])->assertOk();
});

it('updates session time via sanctum api', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->postJson("/api/v1/program-sessions/{$hierarchy['programSession']->id}/update-time", [
        'start_time' => '08:00',
        'end_time' => '09:00',
    ])->assertOk();
});

it('extends admin participant api index for authenticated user', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    \App\Models\Participant::factory()->create(['organization_id' => $org->id]);
    Sanctum::actingAs($user);

    test()->getJson('/api/v1/admin/participants')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('extends admin event days api for authenticated user', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->getJson("/api/v1/admin/events/{$hierarchy['event']->id}/days")
        ->assertOk()
        ->assertJsonStructure(['data']);
});
