<?php

beforeEach(fn () => test()->withoutVite());

$adminPages = [
    ['admin.organizations.index', 'Admin/Organizations/Index', []],
    ['admin.organizations.create', 'Admin/Organizations/Create', []],
    ['admin.events.index', 'Admin/Events/Index', []],
    ['admin.events.create', 'Admin/Events/Create', []],
    ['admin.venues.index', 'Admin/Venues/Index', []],
    ['admin.venues.create', 'Admin/Venues/Create', []],
    ['admin.program-sessions.index', 'Admin/ProgramSessions/Index', []],
    ['admin.program-sessions.create', 'Admin/ProgramSessions/Create', []],
    ['admin.presentations.index', 'Admin/Presentations/Index', []],
    ['admin.presentations.create', 'Admin/Presentations/Create', []],
    ['admin.participants.index', 'Admin/Participants/Index', []],
    ['admin.participants.create', 'Admin/Participants/Create', []],
    ['admin.sponsors.index', 'Admin/Sponsors/Index', []],
    ['admin.sponsors.create', 'Admin/Sponsors/Create', []],
];

it('renders admin index and create pages', function () use ($adminPages) {
    ['user' => $user] = adminContext();

    foreach ($adminPages as [$route, $component, $params]) {
        $response = test()->actingAs($user)->get(route($route, $params));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component($component));
    }
});

it('renders admin show pages', function () {
    $data = fullEventProgram();
    $user = $data['user'];

    $showPages = [
        [route('admin.organizations.show', $data['organization']), 'Admin/Organizations/Show'],
        [route('admin.events.show', $data['event']), 'Admin/Events/Show'],
        [route('admin.events.days.index', $data['event']), 'Admin/EventDays/Index'],
        [route('admin.events.days.create', $data['event']), 'Admin/EventDays/Create'],
        [route('admin.events.days.show', [$data['event'], $data['eventDay']]), 'Admin/EventDays/Show'],
        [route('admin.venues.show', $data['venue']), 'Admin/Venues/Show'],
        [route('admin.program-sessions.show', $data['programSession']), 'Admin/ProgramSessions/Show'],
        [route('admin.presentations.show', $data['presentation']), 'Admin/Presentations/Show'],
        [route('admin.participants.show', $data['participant']), 'Admin/Participants/Show'],
    ];

    foreach ($showPages as [$url, $component]) {
        test()->actingAs($user)->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});

it('renders sponsor show page', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $sponsor = \App\Models\Sponsor::factory()->create(['organization_id' => $org->id]);

    test()->actingAs($user)->get(route('admin.sponsors.show', $sponsor))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Sponsors/Show'));
});

it('renders import index page', function () {
    ['user' => $user] = adminContext();

    test()->actingAs($user)->get(route('admin.import.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Import/Index', false));
});
