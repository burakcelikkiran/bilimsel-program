<?php

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use App\Policies\EventPolicy;

beforeEach(fn () => $this->policy = new EventPolicy);

it('allows admin to update events', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $event))->toBeTrue();
});

it('allows editor in same organization to update events', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $event = Event::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $event))->toBeTrue();
});

it('denies editor from other organization to update events', function () {
    ['user' => $user] = editorContext();
    $event = Event::factory()->create(['organization_id' => Organization::factory()->create()->id]);
    expect($this->policy->update($user, $event))->toBeFalse();
});

it('allows organizer to create events', function () {
    ['user' => $user] = organizerContext();
    expect($this->policy->create($user))->toBeTrue();
});

it('denies editor global user without organizer pivot to create events', function () {
    $user = User::factory()->editor()->create();
    expect($this->policy->create($user))->toBeFalse();
});
