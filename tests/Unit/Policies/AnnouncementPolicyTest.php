<?php

use App\Models\Announcement;
use App\Models\Event;
use App\Policies\AnnouncementPolicy;

beforeEach(fn () => $this->policy = new AnnouncementPolicy);

it('allows admin to create announcements', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $org->id]);

    expect($this->policy->create($user, $event))->toBeTrue();
});

it('allows organizer to create announcements for their event', function () {
    ['organization' => $org, 'user' => $user] = organizerContext();
    $event = Event::factory()->create(['organization_id' => $org->id]);

    expect($this->policy->create($user, $event))->toBeTrue();
});

it('denies editor from creating announcements', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $event = Event::factory()->create(['organization_id' => $org->id]);

    expect($this->policy->create($user, $event))->toBeFalse();
});

it('denies organizer from deleting another organizations announcement', function () {
    ['user' => $user] = organizerContext();
    $announcement = Announcement::factory()->create();

    expect($this->policy->delete($user, $announcement))->toBeFalse();
});
