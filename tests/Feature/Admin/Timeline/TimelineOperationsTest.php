<?php

use App\Models\ProgramSession;
use App\Models\Venue;

it('validates timeline move request', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->postJson(route('admin.timeline.validate-move', $hierarchy['event']->slug), [
            'session_id' => $hierarchy['programSession']->id,
            'target_venue_id' => $hierarchy['venue']->id,
            'target_day_id' => $hierarchy['eventDay']->id,
            'new_start_time' => '10:00',
            'new_end_time' => '11:00',
        ])
        ->assertOk()
        ->assertJsonStructure(['valid', 'message']);
});

it('updates timeline order via update-order endpoint', function () {
    $hierarchy = programHierarchy();
    $secondSession = ProgramSession::factory()->create([
        'venue_id' => $hierarchy['venue']->id,
        'start_time' => '11:00',
        'end_time' => '12:00',
        'sort_order' => 2,
    ]);

    test()->actingAs($hierarchy['user'])
        ->from(route('admin.timeline.edit', $hierarchy['event']->slug))
        ->post(route('admin.timeline.update-order', $hierarchy['event']->slug), [
            'changes' => [
                [
                    'sessionId' => $secondSession->id,
                    'toVenueId' => $hierarchy['venue']->id,
                    'toDayId' => $hierarchy['eventDay']->id,
                    'newSortOrder' => 1,
                    'newStartTime' => '11:00',
                    'newEndTime' => '12:00',
                ],
                [
                    'sessionId' => $hierarchy['programSession']->id,
                    'toVenueId' => $hierarchy['venue']->id,
                    'toDayId' => $hierarchy['eventDay']->id,
                    'newSortOrder' => 2,
                    'newStartTime' => '10:00',
                    'newEndTime' => '11:00',
                ],
            ],
        ])
        ->assertRedirect();

    expect($secondSession->fresh()->sort_order)->toBe(1);
});

it('exports timeline program json', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->post(route('admin.timeline.export', $hierarchy['event']->slug), [
            'format' => 'json',
        ])
        ->assertOk();
});

it('checks program session time conflicts', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->postJson(route('admin.program-sessions.check-time-conflicts'), [
            'venue_id' => $hierarchy['venue']->id,
            'start_time' => '10:30',
            'end_time' => '10:45',
            'exclude_session_id' => $hierarchy['programSession']->id,
        ])
        ->assertOk();
});

it('moves program session to another venue', function () {
    $hierarchy = programHierarchy();
    $targetVenue = Venue::factory()->create(['event_day_id' => $hierarchy['eventDay']->id]);

    test()->actingAs($hierarchy['user'])
        ->postJson(route('admin.program-sessions.move-to-venue', $hierarchy['programSession']), [
            'venue_id' => $targetVenue->id,
        ])
        ->assertOk();

    expect($hierarchy['programSession']->fresh()->venue_id)->toBe($targetVenue->id);
});
