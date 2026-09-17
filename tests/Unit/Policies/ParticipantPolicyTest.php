<?php

use App\Models\Organization;
use App\Models\Participant;
use App\Policies\ParticipantPolicy;

beforeEach(fn () => $this->policy = new ParticipantPolicy);

it('allows admin to update participants', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $participant = Participant::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $participant))->toBeTrue();
});

it('allows editor in same organization to update participants', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $participant = Participant::factory()->create(['organization_id' => $org->id]);
    expect($this->policy->update($user, $participant))->toBeTrue();
});

it('denies editor from other organization to update participants', function () {
    ['user' => $user] = editorContext();
    $participant = Participant::factory()->create(['organization_id' => Organization::factory()->create()->id]);
    expect($this->policy->update($user, $participant))->toBeFalse();
});

it('allows admin to delete participants with presentations', function () {
    $data = fullEventProgram();

    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    expect($this->policy->delete($data['user'], $data['participant']->fresh()))->toBeTrue();
});

it('allows editor with organization membership to create participants', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->create($user))->toBeTrue();
});
