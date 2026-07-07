<?php

it('saves timeline order changes with valid data', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $response = $this->actingAs($hierarchy['user'])->post(
        route('admin.timeline.update-order', $hierarchy['event']->slug),
        [
            'changes' => [
                [
                    'sessionId' => $session->id,
                    'toVenueId' => $hierarchy['venue']->id,
                    'toDayId' => $hierarchy['eventDay']->id,
                    'newSortOrder' => 2,
                    'newStartTime' => '10:00',
                    'newEndTime' => '11:00',
                ],
            ],
        ]
    );

    $response->assertRedirect();

    $this->assertDatabaseHas('program_sessions', [
        'id' => $session->id,
        'sort_order' => 2,
    ]);
});

it('returns validation errors when saving timeline without changes', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.timeline.edit', $hierarchy['event']->slug))
        ->post(route('admin.timeline.update-order', $hierarchy['event']->slug), [
            'changes' => [],
        ]);

    $response->assertSessionHasErrors(['changes']);
});

it('returns validation errors when saving timeline with invalid session id', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.timeline.edit', $hierarchy['event']->slug))
        ->post(route('admin.timeline.update-order', $hierarchy['event']->slug), [
            'changes' => [
                [
                    'sessionId' => 99999,
                    'toVenueId' => $hierarchy['venue']->id,
                    'toDayId' => $hierarchy['eventDay']->id,
                    'newSortOrder' => 1,
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['changes.0.sessionId']);
});

it('renders timeline edit page via inertia', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.timeline.edit', $hierarchy['event']->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Timeline/Edit')
        ->has('event'));
});
