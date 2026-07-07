<?php

use App\Models\Event;
use App\Models\ProgramSession;

it('duplicates an event', function () {
    $hierarchy = programHierarchy();
    $event = $hierarchy['event'];

    $response = test()->actingAs($hierarchy['user'])
        ->post(route('admin.events.duplicate', $event));

    $response->assertRedirect();

    expect(Event::where('organization_id', $event->organization_id)->count())->toBe(2);
    expect(Event::where('name', 'like', '%Kopya%')->exists())->toBeTrue();
});

it('toggles event publish status when requirements met', function () {
    $hierarchy = programHierarchy();
    $event = $hierarchy['event'];

    test()->actingAs($hierarchy['user'])
        ->patch(route('admin.events.toggle-publish', $event))
        ->assertRedirect();

    expect($event->fresh()->is_published)->toBeTrue();
});

it('deletes unpublished event', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $event = Event::factory()->create([
        'organization_id' => $org->id,
        'is_published' => false,
    ]);

    test()->actingAs($user)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'));

    expect(Event::find($event->id))->toBeNull();
});

it('toggles event day status', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->from(route('admin.events.days.show', [$hierarchy['event'], $hierarchy['eventDay']]))
        ->patch(route('admin.events.days.toggle-status', [
            'event' => $hierarchy['event'],
            'eventDay' => $hierarchy['eventDay'],
        ]))
        ->assertRedirect();

    expect($hierarchy['eventDay']->fresh()->is_active)->toBeFalse();
});

it('updates program session sort order', function () {
    $hierarchy = programHierarchy();
    $secondSession = ProgramSession::factory()->create([
        'venue_id' => $hierarchy['venue']->id,
        'start_time' => '11:00',
        'end_time' => '12:00',
        'sort_order' => 2,
    ]);

    test()->actingAs($hierarchy['user'])
        ->patchJson(route('admin.program-sessions.update-sort-order'), [
            'session_ids' => [$secondSession->id, $hierarchy['programSession']->id],
            'venue_id' => $hierarchy['venue']->id,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($secondSession->fresh()->sort_order)->toBe(1);
});

it('rejects participant bulk import without file', function () {
    ['user' => $user] = adminContext();

    test()->actingAs($user)
        ->from(route('admin.import.index'))
        ->post(route('admin.import.participants'), [])
        ->assertSessionHasErrors(['file']);
});
