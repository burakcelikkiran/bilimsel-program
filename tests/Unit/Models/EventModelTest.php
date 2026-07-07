<?php

use App\Models\Event;
use App\Models\Organization;

it('creates unique slug from turkish text', function () {
    $slug = Event::createSlugFromTurkish('Türk Nöroloji Kongresi');

    expect($slug)->toBe('turk-noroloji-kongresi');
});

it('published scope returns only published events', function () {
    $organization = Organization::factory()->create();
    Event::factory()->create(['organization_id' => $organization->id, 'is_published' => false]);
    $published = Event::factory()->published()->create(['organization_id' => $organization->id]);

    $results = Event::published()->pluck('id');

    expect($results)->toContain($published->id);
    expect($results)->toHaveCount(1);
});
