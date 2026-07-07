<?php

use App\Models\Event;
use App\Models\EventDay;

it('stores an event day with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $response = $this->actingAs($user)->post(route('admin.events.days.store', $event), [
        'display_name' => 'Birinci Gün',
        'date' => $event->start_date->toDateString(),
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('event_days', [
        'event_id' => $event->id,
        'display_name' => 'Birinci Gün',
    ]);
});

it('updates an event day with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $eventDay = EventDay::factory()->create([
        'event_id' => $event->id,
        'date' => $event->start_date->toDateString(),
        'display_name' => 'Eski Gün',
    ]);

    $response = $this->actingAs($user)->put(route('admin.events.days.update', [$event, $eventDay]), [
        'display_name' => 'Güncel Gün',
        'date' => $event->start_date->toDateString(),
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('event_days', [
        'id' => $eventDay->id,
        'display_name' => 'Güncel Gün',
    ]);
});

it('returns validation errors when storing event day outside event range', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.events.days.create', $event))
        ->post(route('admin.events.days.store', $event), [
            'display_name' => 'Geçersiz Gün',
            'date' => now()->addMonths(3)->toDateString(),
        ]);

    $response->assertRedirect(route('admin.events.days.create', $event));
    $response->assertSessionHasErrors(['date']);
});

it('returns validation errors when updating event day without display name', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $eventDay = EventDay::factory()->create([
        'event_id' => $event->id,
        'date' => $event->start_date->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.events.days.edit', [$event, $eventDay]))
        ->put(route('admin.events.days.update', [$event, $eventDay]), [
            'display_name' => '',
            'date' => $event->start_date->toDateString(),
        ]);

    $response->assertRedirect(route('admin.events.days.edit', [$event, $eventDay]));
    $response->assertSessionHasErrors(['display_name']);
});
