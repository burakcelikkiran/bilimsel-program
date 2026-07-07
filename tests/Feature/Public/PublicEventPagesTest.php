<?php

use App\Models\Event;
use App\Models\Organization;

beforeEach(fn () => test()->withoutVite());

it('renders public program page for published event', function () {
    $data = fullEventProgram();
    $event = $data['event'];
    $event->update([
        'is_published' => true,
        'slug' => 'public-program-page',
    ]);
    $data['eventDay']->update(['is_active' => true]);
    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    test()->get(route('events.program', $event->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Events/Program')
            ->where('activeTab', 'program')
            ->has('event')
            ->has('statistics')
            ->has('days', 1)
            ->has('days.0.venues', 1)
            ->has('days.0.venues.0.sessions', 1)
            ->has('days.0.venues.0.sessions.0.presentations', 1)
            ->where('days.0.venues.0.sessions.0.presentations.0.title', 'Test Sunumu')
            ->where('days.0.venues.0.sessions.0.presentations.0.speakers.0.id', $data['participant']->id)
        );
});

it('renders public speakers page for published event', function () {
    $data = fullEventProgram();
    $event = $data['event'];
    $event->update([
        'is_published' => true,
        'slug' => 'public-speakers-page',
    ]);
    $data['eventDay']->update(['is_active' => true]);
    $data['programSession']->moderators()->attach($data['participant']->id, ['sort_order' => 1]);
    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    test()->get(route('events.speakers', $event->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Events/Speakers')
            ->where('activeTab', 'speakers')
            ->where('total', 1)
            ->has('event')
            ->has('participants', 1)
            ->where('participants.0.full_name', $data['participant']->full_name)
            ->where('participants.0.participation_count', 2)
            ->has('participants.0.roles', 2)
        );
});

it('renders public venues page', function () {
    $event = Event::factory()->published()->create(['slug' => 'public-venues-page']);

    test()->get(route('events.venues', $event->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Events/Show')
            ->where('activeTab', 'venues')
            ->has('event')
        );
});

it('renders public sponsors page', function () {
    $event = Event::factory()->published()->create(['slug' => 'public-sponsors-page']);

    test()->get(route('events.sponsors', $event->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Events/Show')
            ->where('activeTab', 'sponsors')
            ->has('event')
        );
});

it('returns 404 for unpublished event public pages', function () {
    $event = Event::factory()->create([
        'organization_id' => Organization::factory()->create()->id,
        'is_published' => false,
        'slug' => 'gizli-public-etkinlik',
    ]);

    test()->get(route('events.show', $event->slug))->assertNotFound();
    test()->get(route('events.program', $event->slug))->assertNotFound();
});
