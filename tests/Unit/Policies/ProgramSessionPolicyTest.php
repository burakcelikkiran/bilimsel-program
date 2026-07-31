<?php

use App\Policies\ProgramSessionPolicy;

beforeEach(fn () => $this->policy = new ProgramSessionPolicy);

it('allows admin to update program sessions', function () {
    $hierarchy = programHierarchy();
    expect($this->policy->update($hierarchy['user'], $hierarchy['programSession']))->toBeTrue();
});

it('allows editor in same organization to update program sessions', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $hierarchy = programHierarchy($org, $user);
    expect($this->policy->update($user, $hierarchy['programSession']))->toBeTrue();
});

it('denies editor from other organization to update program sessions', function () {
    ['user' => $user] = editorContext();
    $hierarchy = programHierarchy();
    expect($this->policy->update($user, $hierarchy['programSession']))->toBeFalse();
});

it('allows deleting session with presentations for admin', function () {
    $data = fullEventProgram();
    expect($this->policy->delete($data['user'], $data['programSession']))->toBeTrue();
});
