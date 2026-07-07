<?php

use App\Models\Organization;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Policies\SponsorPolicy;

beforeEach(fn () => $this->policy = new SponsorPolicy);

it('allows admin to update sponsors', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $sponsor = Sponsor::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $sponsor))->toBeTrue();
});

it('allows editor in same organization to update sponsors', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $sponsor = Sponsor::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $sponsor))->toBeTrue();
});

it('denies editor from other organization to update sponsors', function () {
    ['user' => $user] = editorContext();
    $sponsor = Sponsor::factory()->create(['organization_id' => Organization::factory()->create()->id]);
    expect($this->policy->update($user, $sponsor))->toBeFalse();
});

it('prevents deleting sponsor with linked sessions', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $hierarchy = programHierarchy($org, $user);
    $sponsor = Sponsor::factory()->create(['organization_id' => $org->id]);
    ProgramSession::factory()->create([
        'venue_id' => $hierarchy['venue']->id,
        'sponsor_id' => $sponsor->id,
    ]);
    expect($this->policy->delete($user, $sponsor))->toBeFalse();
});
