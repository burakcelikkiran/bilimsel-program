<?php

use App\Models\Event;
use App\Models\EventDay;
use App\Models\ProgramSession;

it('stores an event with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)->post(route('admin.events.store'), [
        'organization_id' => $organization->id,
        'title' => 'Yeni Etkinlik',
        'description' => 'Açıklama',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
        'location' => 'Ankara',
        'is_published' => false,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('events', [
        'organization_id' => $organization->id,
        'name' => 'Yeni Etkinlik',
        'location' => 'Ankara',
    ]);
});

it('updates an event with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Eski Etkinlik',
    ]);

    $response = $this->actingAs($user)
        ->put(route('admin.events.update', $event), [
            'organization_id' => $organization->id,
            'title' => 'Güncel Etkinlik',
            'description' => 'Açıklama',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'location' => 'İstanbul',
            'is_published' => false,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Güncel Etkinlik',
        'location' => 'İstanbul',
    ]);
});

it('updates event with sessions and past start date without changing dates', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->subMonths(2)->addDays(2)->toDateString(),
    ]);

    $eventDay = EventDay::factory()->create(['event_id' => $event->id]);
    $venue = \App\Models\Venue::factory()->create(['event_day_id' => $eventDay->id]);
    ProgramSession::factory()->create(['venue_id' => $venue->id]);

    $response = $this->actingAs($user)->put(route('admin.events.update', $event), [
        'organization_id' => $organization->id,
        'title' => $event->name,
        'description' => 'Güncellenmiş açıklama',
        'start_date' => $event->start_date->toDateString(),
        'end_date' => $event->end_date->toDateString(),
        'location' => 'Yeni Konum',
        'is_published' => false,
    ]);

    $response->assertRedirect(route('admin.events.show', $event));

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'location' => 'Yeni Konum',
        'description' => 'Güncellenmiş açıklama',
    ]);
});

it('returns validation errors when updating event with invalid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($user)
        ->from(route('admin.events.edit', $event))
        ->put(route('admin.events.update', $event), [
            'organization_id' => $organization->id,
            'title' => '',
            'start_date' => now()->addWeeks(2)->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ]);

    $response->assertRedirect(route('admin.events.edit', $event));
    $response->assertSessionHasErrors(['title', 'end_date']);
});

it('returns validation errors when storing event without title', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)
        ->from(route('admin.events.create'))
        ->post(route('admin.events.store'), [
            'organization_id' => $organization->id,
            'title' => '',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
        ]);

    $response->assertRedirect(route('admin.events.create'));
    $response->assertSessionHasErrors(['title']);
});

it('renders event edit page via inertia', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($user)->get(route('admin.events.edit', $event));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Events/Edit')
        ->has('event')
        ->has('organizations'));
});
