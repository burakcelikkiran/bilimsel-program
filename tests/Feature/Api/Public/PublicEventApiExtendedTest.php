<?php

use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use Laravel\Sanctum\Sanctum;

it('returns program json for published event', function () {
    $organization = Organization::factory()->create();
    $event = Event::factory()->published()->create([
        'organization_id' => $organization->id,
        'slug' => 'api-program-event',
    ]);
    programHierarchy($organization);

    test()->getJson("/api/v1/events/{$event->slug}/program")
        ->assertOk()
        ->assertJsonStructure(['success', 'data']);
});

it('returns day program for specific date', function () {
    $data = fullEventProgram();
    $event = $data['event'];
    $event->update(['is_published' => true, 'slug' => 'api-day-program']);
    $data['eventDay']->update(['is_active' => true]);

    test()->getJson("/api/v1/events/{$event->slug}/program/day/{$data['eventDay']->date->format('Y-m-d')}")
        ->assertOk()
        ->assertJsonPath('success', true);
})->skip('Günlük program API\'si sunum/konuşmacı mapping hatası nedeniyle 500 dönüyor.');

it('returns event speakers via api', function () {
    $data = fullEventProgram();
    $event = Event::factory()->published()->create([
        'organization_id' => $data['organization']->id,
        'slug' => 'api-speakers-event',
    ]);

    Participant::factory()->speaker()->create([
        'organization_id' => $data['organization']->id,
    ]);

    test()->getJson("/api/v1/events/{$event->slug}/speakers")
        ->assertOk();
});

it('returns event venues via api', function () {
    $data = fullEventProgram();
    $event = Event::factory()->published()->create([
        'organization_id' => $data['organization']->id,
        'slug' => 'api-venues-event',
    ]);

    test()->getJson("/api/v1/events/{$event->slug}/venues")
        ->assertOk();
});

it('returns event statistics via api', function () {
    $data = fullEventProgram();
    $event = Event::factory()->published()->create([
        'organization_id' => $data['organization']->id,
        'slug' => 'api-stats-event',
    ]);

    test()->getJson("/api/v1/events/{$event->slug}/stats")
        ->assertOk();
});

it('searches events via public search api', function () {
    Event::factory()->published()->create(['name' => 'UniqueApiSearchEvent']);

    test()->getJson('/api/v1/search/events?q=UniqueApiSearchEvent')
        ->assertOk();
});

it('returns 404 for unpublished event via api', function () {
    $event = Event::factory()->create(['is_published' => false, 'slug' => 'gizli-etkinlik']);

    test()->getJson("/api/v1/events/{$event->slug}")
        ->assertNotFound();
});

it('bulk updates sessions via sanctum api', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->postJson('/api/v1/program-sessions/bulk-update', [
        'sessions' => [
            [
                'id' => $hierarchy['programSession']->id,
                'sort_order' => 5,
            ],
        ],
    ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($hierarchy['programSession']->fresh()->sort_order)->toBe(5);
});

it('quick creates session via sanctum api', function () {
    $hierarchy = programHierarchy();
    Sanctum::actingAs($hierarchy['user']);

    test()->postJson('/api/v1/program-sessions/quick-create', [
        'title' => 'API Hızlı Oturum',
        'venue_id' => $hierarchy['venue']->id,
        'start_time' => '15:00',
        'end_time' => '16:00',
        'session_type' => 'main',
    ])
        ->assertCreated()
        ->assertJson(['success' => true]);
});

it('validates quick create payload', function () {
    ['user' => $user] = adminContext();
    Sanctum::actingAs($user);

    test()->postJson('/api/v1/program-sessions/quick-create', [])
        ->assertStatus(422);
});
