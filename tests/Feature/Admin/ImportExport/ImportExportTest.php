<?php

it('exports event program via export controller', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->get(route('admin.export.events.program-excel', $hierarchy['event']))
        ->assertOk();
});

it('downloads import template', function () {
    ['user' => $user] = adminContext();

    test()->actingAs($user)
        ->get(route('admin.import.templates', 'participants'))
        ->assertOk();
});

it('exports timeline json via timeline controller', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->post(route('admin.timeline.export', $hierarchy['event']->slug), [
            'format' => 'program_json',
        ])
        ->assertOk();
});

it('exports event statistics excel via export controller', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->get(route('admin.export.events.statistics-excel', $hierarchy['event']))
        ->assertOk();
});
