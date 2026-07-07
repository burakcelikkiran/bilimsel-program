<?php

it('stores a program session with valid data', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.program-sessions.store'), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Yeni Oturum',
        'description' => 'Oturum açıklaması',
        'start_time' => '14:00',
        'end_time' => '15:00',
        'session_type' => 'main',
        'moderator_title' => 'Oturum Başkanı',
        'is_break' => false,
        'moderator_ids' => [],
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('program_sessions', [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Yeni Oturum',
        'start_time' => '14:00:00',
    ]);
});

it('updates a program session with valid data', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $response = $this->actingAs($hierarchy['user'])->put(route('admin.program-sessions.update', $session), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Güncel Oturum',
        'description' => 'Güncel açıklama',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'session_type' => 'main',
        'moderator_title' => 'Başkan',
        'is_break' => false,
        'moderator_ids' => [],
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('program_sessions', [
        'id' => $session->id,
        'title' => 'Güncel Oturum',
    ]);
});

it('returns validation errors when storing program session without title', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.create'))
        ->post(route('admin.program-sessions.store'), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => '',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'session_type' => 'main',
        ]);

    $response->assertRedirect(route('admin.program-sessions.create'));
    $response->assertSessionHasErrors(['title']);
});

it('returns validation errors when updating program session with end time before start time', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.edit', $session))
        ->put(route('admin.program-sessions.update', $session), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => $session->title,
            'start_time' => '15:00',
            'end_time' => '14:00',
            'session_type' => 'main',
        ]);

    $response->assertRedirect(route('admin.program-sessions.edit', $session));
    $response->assertSessionHasErrors(['end_time']);
});

it('renders program session edit page via inertia', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.program-sessions.edit', $hierarchy['programSession']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Edit')
        ->has('programSession'));
});

it('renders program session create page with venue context preselected', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(
        route('admin.program-sessions.create', ['venue_id' => $hierarchy['venue']->id])
    );

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Create')
        ->where('selectedEventId', $hierarchy['event']->id)
        ->where('selectedEventDayId', $hierarchy['eventDay']->id)
        ->where('selectedVenueId', (string) $hierarchy['venue']->id)
        ->has('eventDays', 1)
        ->has('venues', 1)
        ->has('selectedEvent')
        ->has('selectedEventDay'));
});
