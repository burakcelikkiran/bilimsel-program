<?php

use App\Policies\EventDayPolicy;

beforeEach(fn () => $this->policy = new EventDayPolicy);

it('allows admin to update event days', function () {
    $hierarchy = programHierarchy();
    expect($this->policy->update($hierarchy['user'], $hierarchy['eventDay']))->toBeTrue();
});

it('allows editor in same organization to update event days', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $hierarchy = programHierarchy($org, $user);
    expect($this->policy->update($user, $hierarchy['eventDay']))->toBeTrue();
});

it('denies editor from other organization to update event days', function () {
    ['user' => $user] = editorContext();
    $hierarchy = programHierarchy();
    expect($this->policy->update($user, $hierarchy['eventDay']))->toBeFalse();
});

it('prevents deleting event day with program sessions', function () {
    $hierarchy = programHierarchy();
    expect($this->policy->delete($hierarchy['user'], $hierarchy['eventDay']))->toBeFalse();
});
