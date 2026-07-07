<?php

it('stores a venue with valid data', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.venues.store'), [
        'event_day_id' => $hierarchy['eventDay']->id,
        'name' => 'Yeni Salon',
        'display_name' => 'Yeni Salon',
        'capacity' => 200,
        'color' => '#3B82F6',
        'sort_order' => 1,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('venues', [
        'event_day_id' => $hierarchy['eventDay']->id,
        'name' => 'yeni-salon',
        'display_name' => 'Yeni Salon',
        'capacity' => 200,
    ]);
});

it('updates a venue with valid data', function () {
    $hierarchy = programHierarchy();
    $venue = $hierarchy['venue'];

    $response = $this->actingAs($hierarchy['user'])->patch(route('admin.venues.update', $venue), [
        'event_day_id' => $hierarchy['eventDay']->id,
        'name' => 'Güncel Salon',
        'display_name' => 'Güncel Salon',
        'capacity' => 350,
        'color' => '#EF4444',
        'sort_order' => 2,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('venues', [
        'id' => $venue->id,
        'name' => 'guncel-salon',
        'display_name' => 'Güncel Salon',
        'capacity' => 350,
    ]);
});

it('returns validation errors when storing venue without name', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.venues.create'))
        ->post(route('admin.venues.store'), [
            'event_day_id' => $hierarchy['eventDay']->id,
            'name' => '',
        ]);

    $response->assertRedirect(route('admin.venues.create'));
    $response->assertSessionHasErrors(['name']);
});

it('returns validation errors when updating venue with invalid color', function () {
    $hierarchy = programHierarchy();
    $venue = $hierarchy['venue'];

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.venues.edit', $venue))
        ->patch(route('admin.venues.update', $venue), [
            'event_day_id' => $hierarchy['eventDay']->id,
            'name' => $venue->name,
            'color' => 'invalid-color',
        ]);

    $response->assertRedirect(route('admin.venues.edit', $venue));
    $response->assertSessionHasErrors(['color']);
});

it('renders venue edit page via inertia', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.venues.edit', $hierarchy['venue']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Venues/Edit')
        ->has('venue'));
});
