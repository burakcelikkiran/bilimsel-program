<?php

use App\Models\Organization;

it('stores an organization with valid data', function () {
    ['user' => $user] = adminContext();

    $response = $this->actingAs($user)->post(route('admin.organizations.store'), [
        'name' => 'Yeni Organizasyon',
        'description' => 'Test açıklama',
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('organizations', [
        'name' => 'Yeni Organizasyon',
        'description' => 'Test açıklama',
    ]);
});

it('updates an organization with valid data', function () {
    ['user' => $user] = adminContext();
    $organization = Organization::factory()->create(['name' => 'Eski Ad']);

    $response = $this->actingAs($user)
        ->from(route('admin.organizations.edit', $organization))
        ->post(route('admin.organizations.update', $organization), [
            '_method' => 'PUT',
            'name' => 'Güncel Organizasyon',
            'description' => 'Güncel açıklama',
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.organizations.show', $organization));

    $this->assertDatabaseHas('organizations', [
        'id' => $organization->id,
        'name' => 'Güncel Organizasyon',
    ]);
});

it('returns validation errors when storing organization without name', function () {
    ['user' => $user] = adminContext();

    $response = $this->actingAs($user)
        ->from(route('admin.organizations.create'))
        ->post(route('admin.organizations.store'), [
            'name' => '',
        ]);

    $response->assertRedirect(route('admin.organizations.create'));
    $response->assertSessionHasErrors(['name']);
});

it('returns validation errors when updating organization with invalid email', function () {
    ['user' => $user] = adminContext();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('admin.organizations.edit', $organization))
        ->post(route('admin.organizations.update', $organization), [
            '_method' => 'PUT',
            'name' => $organization->name,
            'contact_email' => 'invalid-email',
            'is_active' => true,
        ]);

    $response->assertRedirect(route('admin.organizations.edit', $organization));
    $response->assertSessionHasErrors(['contact_email']);
});

it('renders organization edit page via inertia', function () {
    ['user' => $user] = adminContext();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.organizations.edit', $organization));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Organizations/Edit')
        ->has('organization'));
});
