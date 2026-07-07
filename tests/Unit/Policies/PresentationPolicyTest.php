<?php

use App\Policies\PresentationPolicy;

beforeEach(fn () => $this->policy = new PresentationPolicy);

it('allows admin to update presentations', function () {
    $data = fullEventProgram();
    expect($this->policy->update($data['user'], $data['presentation']))->toBeTrue();
});

it('allows editor in same organization to update presentations', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $data = fullEventProgram($org, $user);
    expect($this->policy->update($user, $data['presentation']))->toBeTrue();
});

it('denies editor from other organization to update presentations', function () {
    ['user' => $user] = editorContext();
    $data = fullEventProgram();
    expect($this->policy->update($user, $data['presentation']))->toBeFalse();
});

it('allows editor with membership to create presentations', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->create($user))->toBeTrue();
});
