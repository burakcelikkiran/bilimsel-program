<?php

use App\Models\User;
use App\Policies\FileUploadPolicy;

beforeEach(fn () => $this->policy = new FileUploadPolicy);

it('allows admin to upload participant photos', function () {
    ['user' => $user] = adminContext();
    expect($this->policy->uploadParticipantPhoto($user))->toBeTrue();
});

it('allows editor to upload participant photos', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->uploadParticipantPhoto($user))->toBeTrue();
});

it('denies editor without organization to upload participant photos', function () {
    $user = User::factory()->editor()->create();
    expect($this->policy->uploadParticipantPhoto($user))->toBeFalse();
});

it('allows organizer to upload event banners', function () {
    ['user' => $user] = organizerContext();
    expect($this->policy->uploadEventBanner($user))->toBeTrue();
});

it('denies editor from uploading event banners', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->uploadEventBanner($user))->toBeFalse();
});
