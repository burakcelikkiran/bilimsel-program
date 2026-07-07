<?php

use App\Models\ProgramSessionCategory;
use App\Policies\ProgramSessionCategoryPolicy;

beforeEach(fn () => $this->policy = new ProgramSessionCategoryPolicy);

it('allows admin to update categories', function () {
    $hierarchy = programHierarchy();
    $category = ProgramSessionCategory::create([
        'event_id' => $hierarchy['event']->id,
        'name' => 'Kategori A',
        'color' => '#3B82F6',
    ]);
    expect($this->policy->update($hierarchy['user'], $category))->toBeTrue();
});

it('allows editor in same organization to update categories', function () {
    ['organization' => $org, 'user' => $user] = editorContext();
    $hierarchy = programHierarchy($org, $user);
    $category = ProgramSessionCategory::create([
        'event_id' => $hierarchy['event']->id,
        'name' => 'Kategori B',
        'color' => '#EF4444',
    ]);
    expect($this->policy->update($user, $category))->toBeTrue();
});

it('denies editor from other organization to update categories', function () {
    ['user' => $user] = editorContext();
    $hierarchy = programHierarchy();
    $category = ProgramSessionCategory::create([
        'event_id' => $hierarchy['event']->id,
        'name' => 'Kategori C',
        'color' => '#10B981',
    ]);
    expect($this->policy->update($user, $category))->toBeFalse();
});

it('allows editor with membership to create categories', function () {
    ['user' => $user] = editorContext();
    expect($this->policy->create($user))->toBeTrue();
});
