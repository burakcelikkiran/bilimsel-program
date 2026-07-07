<?php

use App\Models\Venue;

it('moves session via drag drop api', function () {
    $hierarchy = programHierarchy();
    $targetVenue = Venue::factory()->create(['event_day_id' => $hierarchy['eventDay']->id]);

    test()->actingAs($hierarchy['user'])
        ->patchJson(route('admin.drag-drop.move-session'), [
            'session_id' => $hierarchy['programSession']->id,
            'target_venue_id' => $targetVenue->id,
            'new_start_time' => '14:00',
            'new_end_time' => '15:00',
            'force_move' => true,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($hierarchy['programSession']->fresh()->venue_id)->toBe($targetVenue->id);
});

it('validates drag drop move payload', function () {
    ['user' => $user] = adminContext();

    test()->actingAs($user)
        ->patchJson(route('admin.drag-drop.move-session'), [])
        ->assertStatus(422);
});

it('saves drag drop layout', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->postJson(route('admin.drag-drop.save-layout'), [
            'event_id' => $hierarchy['event']->id,
            'layout_name' => 'Test Düzen',
            'description' => 'Test açıklama',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('loads drag drop layout', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->getJson(route('admin.drag-drop.load-layout', $hierarchy['event']->slug))
        ->assertOk();
});

it('bulk updates sessions via drag drop', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->patchJson(route('admin.drag-drop.bulk-update'), [
            'operations' => [
                [
                    'type' => 'update_time',
                    'data' => [
                        'session_id' => $hierarchy['programSession']->id,
                        'start_time' => '09:00',
                        'end_time' => '10:00',
                    ],
                ],
            ],
            'force_update' => true,
        ])
        ->assertOk();
});

it('checks drag drop conflicts', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->postJson(route('admin.drag-drop.check-conflicts'), [
            'event_id' => $hierarchy['event']->id,
            'session_moves' => [
                [
                    'session_id' => $hierarchy['programSession']->id,
                    'target_venue_id' => $hierarchy['venue']->id,
                    'new_start_time' => '10:00',
                    'new_end_time' => '11:00',
                ],
            ],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);
});
