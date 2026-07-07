<?php

use App\Models\Sponsor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a sponsor with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)->post(route('admin.sponsors.store'), [
        'organization_id' => $organization->id,
        'name' => 'Test Sponsor',
        'sponsor_level' => 'gold',
        'contact_email' => 'sponsor@example.com',
        'website' => 'https://sponsor.example.com',
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sponsors', [
        'organization_id' => $organization->id,
        'name' => 'TEST SPONSOR',
        'sponsor_level' => 'gold',
    ]);
});

it('updates a sponsor with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $sponsor = Sponsor::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'ESKI SPONSOR',
        'sponsor_level' => 'silver',
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.sponsors.edit', $sponsor))
        ->post(route('admin.sponsors.update', $sponsor), [
            '_method' => 'PUT',
            'organization_id' => $organization->id,
            'name' => 'Güncel Sponsor',
            'sponsor_level' => 'platinum',
            'contact_email' => 'guncel@example.com',
            'is_active' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sponsors', [
        'id' => $sponsor->id,
        'sponsor_level' => 'platinum',
    ]);

    expect($sponsor->fresh()->name)->toBe('GüNCEL SPONSOR');
});

it('updates a sponsor with logo upload via multipart form', function () {
    Storage::fake('public');

    ['organization' => $organization, 'user' => $user] = adminContext();

    $sponsor = Sponsor::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)->post(route('admin.sponsors.update', $sponsor), [
        '_method' => 'PUT',
        'organization_id' => $organization->id,
        'name' => $sponsor->name,
        'sponsor_level' => $sponsor->sponsor_level,
        'logo' => UploadedFile::fake()->image('logo.png'),
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $sponsor->refresh();
    expect($sponsor->logo)->not->toBeNull();
});

it('returns validation errors when storing sponsor with invalid level', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)
        ->from(route('admin.sponsors.create'))
        ->post(route('admin.sponsors.store'), [
            'organization_id' => $organization->id,
            'name' => 'Test Sponsor',
            'sponsor_level' => 'invalid-level',
        ]);

    $response->assertRedirect(route('admin.sponsors.create'));
    $response->assertSessionHasErrors(['sponsor_level']);
});

it('returns validation errors when updating sponsor with invalid website', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $sponsor = Sponsor::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.sponsors.edit', $sponsor))
        ->post(route('admin.sponsors.update', $sponsor), [
            '_method' => 'PUT',
            'organization_id' => $organization->id,
            'name' => $sponsor->name,
            'sponsor_level' => $sponsor->sponsor_level,
            'website' => 'not-a-valid-url',
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.sponsors.edit', $sponsor));
    $response->assertSessionHasErrors(['website']);
});

it('renders sponsor edit page via inertia', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $sponsor = Sponsor::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.sponsors.edit', $sponsor));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Sponsors/Edit')
        ->has('sponsor'));
});
