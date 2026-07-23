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

it('includes participant presentations and moderated sessions on show page', function () {
    $data = fullEventProgram();
    $participant = $data['participant'];
    $presentation = $data['presentation'];

    $participant->update(['is_speaker' => true]);
    $presentation->speakers()->attach($participant->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    $data['programSession']->moderators()->attach($participant->id, [
        'sort_order' => 1,
    ]);
    $participant->update(['is_moderator' => true]);

    $response = $this->actingAs($data['user'])->get(route('admin.participants.show', $participant));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Participants/Show')
        ->has('participant.presentations', 1)
        ->has('participant.moderated_sessions', 1)
        ->where('participant.presentations.0.id', $presentation->id)
        ->where('participant.presentations.0.title', $presentation->title)
        ->where('participant.moderated_sessions.0.id', $data['programSession']->id));
});
