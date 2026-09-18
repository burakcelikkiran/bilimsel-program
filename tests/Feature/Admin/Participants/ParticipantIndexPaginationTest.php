<?php

use App\Models\Participant;

beforeEach(fn () => test()->withoutVite());

it('respects per_page query parameter on participants index', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    Participant::factory()->count(25)->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.participants.index', [
        'per_page' => 200,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Participants/Index')
        ->where('participants.per_page', 200)
        ->where('filters.per_page', 200)
        ->has('participants.data', 25)
    );
});

it('defaults to 20 participants per page', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    Participant::factory()->count(25)->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.participants.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Participants/Index')
        ->where('participants.per_page', 20)
        ->where('filters.per_page', 20)
        ->has('participants.data', 20)
    );
});

it('caps per_page at 200', function () {
    ['user' => $user] = adminContext();

    $response = $this->actingAs($user)->get(route('admin.participants.index', [
        'per_page' => 500,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('participants.per_page', 200)
        ->where('filters.per_page', 200)
    );
});
