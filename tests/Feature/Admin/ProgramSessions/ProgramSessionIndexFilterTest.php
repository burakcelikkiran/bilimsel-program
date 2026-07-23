<?php

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Venue;

beforeEach(fn () => test()->withoutVite());

it('filters program sessions by event slug query parameter', function () {
    $hierarchy = programHierarchy();

    $otherEvent = Event::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
    ]);
    $otherEventDay = EventDay::factory()->create([
        'event_id' => $otherEvent->id,
        'date' => $otherEvent->start_date->toDateString(),
    ]);
    $otherVenue = Venue::factory()->create(['event_day_id' => $otherEventDay->id]);
    ProgramSession::factory()->create(['venue_id' => $otherVenue->id]);

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index', ['event' => $hierarchy['event']->slug]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Index')
        ->where('filters.event_id', $hierarchy['event']->id)
        ->has('sessions.data', 1)
        ->where('sessions.data.0.id', $hierarchy['programSession']->id)
    );
});

it('filters program sessions by event id query parameter', function () {
    $hierarchy = programHierarchy();

    $otherEvent = Event::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
    ]);
    $otherEventDay = EventDay::factory()->create([
        'event_id' => $otherEvent->id,
        'date' => $otherEvent->start_date->toDateString(),
    ]);
    $otherVenue = Venue::factory()->create(['event_day_id' => $otherEventDay->id]);
    ProgramSession::factory()->create(['venue_id' => $otherVenue->id]);

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index', ['event_id' => $hierarchy['event']->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('filters.event_id', $hierarchy['event']->id)
        ->has('sessions.data', 1)
        ->where('sessions.data.0.id', $hierarchy['programSession']->id)
    );
});

it('filters program sessions by event day id query parameter', function () {
    $hierarchy = programHierarchy();

    $otherEventDay = EventDay::factory()->create([
        'event_id' => $hierarchy['event']->id,
        'date' => $hierarchy['event']->start_date->copy()->addDay()->toDateString(),
    ]);
    $otherVenue = Venue::factory()->create(['event_day_id' => $otherEventDay->id]);
    ProgramSession::factory()->create(['venue_id' => $otherVenue->id]);

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index', ['event_day_id' => $hierarchy['eventDay']->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('filters.event_day_id', (string) $hierarchy['eventDay']->id)
        ->has('sessions.data', 1)
        ->where('sessions.data.0.id', $hierarchy['programSession']->id)
    );
});

it('returns event day filter options scoped to selected event', function () {
    $hierarchy = programHierarchy();

    $otherEvent = Event::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
    ]);
    EventDay::factory()->create([
        'event_id' => $otherEvent->id,
        'date' => $otherEvent->start_date->toDateString(),
    ]);

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index', ['event_id' => $hierarchy['event']->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('filter_options.event_days', 1)
        ->where('filter_options.event_days.0.id', $hierarchy['eventDay']->id)
        ->where('filter_options.event_days.0.name', $hierarchy['eventDay']->display_name)
    );
});

it('returns event filter options with id and name keys', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('filter_options.events', 1)
        ->where('filter_options.events.0.id', $hierarchy['event']->id)
        ->where('filter_options.events.0.name', $hierarchy['event']->name)
        ->where('filter_options.events.0.slug', $hierarchy['event']->slug)
    );
});

it('searches program sessions by moderator name', function () {
    $hierarchy = programHierarchy();

    $moderator = Participant::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
        'first_name' => 'UniqueModerator',
        'last_name' => 'TestName',
    ]);

    $hierarchy['programSession']->moderators()->attach($moderator->id, ['sort_order' => 1]);

    ProgramSession::factory()->create(['venue_id' => $hierarchy['venue']->id]);

    $response = $this->actingAs($hierarchy['user'])
        ->get(route('admin.program-sessions.index', ['search' => 'UniqueModerator']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('sessions.data', 1)
        ->where('sessions.data.0.id', $hierarchy['programSession']->id)
    );
});
