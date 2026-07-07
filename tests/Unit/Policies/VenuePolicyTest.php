<?php

use App\Models\User;
use App\Policies\VenuePolicy;

beforeEach(fn () => $this->policy = new VenuePolicy);

it('allows admin to create venues', function () {
    ['user' => $user] = adminContext();
    expect($this->policy->create($user))->toBeTrue();
});

it('allows editor with organization membership to create venues', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->create($user))->toBeTrue();
});

it('denies editor without organization membership to create venues', function () {
    $user = User::factory()->editor()->create();
    expect($this->policy->create($user))->toBeFalse();
});

it('allows admin to update venues', function () {
    $hierarchy = programHierarchy();
    expect($this->policy->update($hierarchy['user'], $hierarchy['venue']))->toBeTrue();
});
