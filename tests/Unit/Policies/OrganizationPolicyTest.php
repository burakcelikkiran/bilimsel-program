<?php

use App\Policies\OrganizationPolicy;

beforeEach(fn () => $this->policy = new OrganizationPolicy);

it('allows only admin to create organizations', function () {
    ['user' => $admin] = adminContext();
    ['user' => $editor] = editorContext();
    expect($this->policy->create($admin))->toBeTrue();
    expect($this->policy->create($editor))->toBeFalse();
});

it('allows organizer to update their organization', function () {
    ['organization' => $org, 'user' => $user] = organizerContext();
    expect($this->policy->update($user, $org))->toBeTrue();
});

it('denies editor from updating organization', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    expect($this->policy->update($user, $org))->toBeFalse();
});

it('allows members to view their organization', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    expect($this->policy->view($user, $org))->toBeTrue();
});
