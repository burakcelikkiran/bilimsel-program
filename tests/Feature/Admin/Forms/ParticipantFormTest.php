<?php

use App\Models\Participant;

it('stores a participant with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)->post(route('admin.participants.store'), [
        'organization_id' => $organization->id,
        'first_name' => 'Yeni',
        'last_name' => 'Katılımcı',
        'title' => 'Dr.',
        'email' => 'yeni@example.com',
        'phone' => '5551234567',
        'affiliation' => 'Test Kurum',
        'bio' => 'Bio',
        'is_speaker' => true,
        'is_moderator' => false,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('participants', [
        'organization_id' => $organization->id,
        'first_name' => 'Yeni',
        'email' => 'yeni@example.com',
    ]);
});

it('updates a participant with valid data', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $participant = Participant::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'Eski',
        'last_name' => 'Ad',
    ]);

    $response = $this->actingAs($user)
        ->put(route('admin.participants.update', $participant), [
            'organization_id' => $organization->id,
            'first_name' => 'Yeni',
            'last_name' => 'Ad',
            'title' => 'Dr.',
            'email' => 'yeni@example.com',
            'phone' => '5551234567',
            'affiliation' => 'Test Kurum',
            'bio' => 'Bio',
            'is_speaker' => true,
            'is_moderator' => false,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('participants', [
        'id' => $participant->id,
        'first_name' => 'Yeni',
        'email' => 'yeni@example.com',
    ]);
});

it('returns validation errors when storing participant without first name', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)
        ->from(route('admin.participants.create'))
        ->post(route('admin.participants.store'), [
            'organization_id' => $organization->id,
            'first_name' => '',
            'last_name' => 'Ad',
            'email' => 'invalid-email',
        ]);

    $response->assertRedirect(route('admin.participants.create'));
    $response->assertSessionHasErrors(['first_name', 'email']);
});

it('returns validation errors when updating participant with invalid email', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $participant = Participant::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.participants.edit', $participant))
        ->put(route('admin.participants.update', $participant), [
            'organization_id' => $organization->id,
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
        ]);

    $response->assertRedirect(route('admin.participants.edit', $participant));
    $response->assertSessionHasErrors(['first_name', 'email']);
});

it('renders participant edit page via inertia', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $participant = Participant::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.participants.edit', $participant));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Participants/Edit')
        ->has('participant'));
});
