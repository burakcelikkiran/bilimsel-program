<?php

use App\Models\Presentation;
use App\Models\ProgramSession;

it('stores a presentation with valid data', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.presentations.store'), [
        'program_session_id' => $hierarchy['programSession']->id,
        'title' => 'Yeni Sunum Başlığı',
        'abstract' => 'Sunum özeti',
        'start_time' => '10:15',
        'end_time' => '10:45',
        'duration_minutes' => 30,
        'presentation_type' => 'oral',
        'speakers' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('presentations', [
        'program_session_id' => $hierarchy['programSession']->id,
        'title' => 'Yeni Sunum Başlığı',
    ]);
});

it('updates a presentation with valid data', function () {
    $hierarchy = programHierarchy();

    $presentation = Presentation::factory()->create([
        'program_session_id' => $hierarchy['programSession']->id,
        'title' => 'Eski Sunum',
        'start_time' => '10:00',
        'end_time' => '10:30',
    ]);

    $response = $this->actingAs($hierarchy['user'])->put(route('admin.presentations.update', $presentation), [
        'program_session_id' => $hierarchy['programSession']->id,
        'title' => 'Güncel Sunum Başlığı',
        'abstract' => 'Güncel özet',
        'start_time' => '10:00',
        'end_time' => '10:30',
        'duration_minutes' => 30,
        'presentation_type' => 'oral',
        'speakers' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('presentations', [
        'id' => $presentation->id,
        'title' => 'Güncel Sunum Başlığı',
    ]);
});

it('returns validation errors when storing presentation without title', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.presentations.create'))
        ->post(route('admin.presentations.store'), [
            'program_session_id' => $hierarchy['programSession']->id,
            'title' => '',
        ]);

    $response->assertRedirect(route('admin.presentations.create'));
    $response->assertSessionHasErrors(['title']);
});

it('returns validation errors when storing presentation for break session', function () {
    $hierarchy = programHierarchy();

    $breakSession = ProgramSession::factory()->create([
        'venue_id' => $hierarchy['venue']->id,
        'start_time' => '12:00',
        'end_time' => '12:30',
        'session_type' => 'break',
        'is_break' => true,
    ]);

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.presentations.create'))
        ->post(route('admin.presentations.store'), [
            'program_session_id' => $breakSession->id,
            'title' => 'Ara Oturum Sunumu',
        ]);

    $response->assertRedirect(route('admin.presentations.create'));
    $response->assertSessionHasErrors(['error']);
});

it('renders presentation edit page via inertia', function () {
    $hierarchy = programHierarchy();

    $presentation = Presentation::factory()->create([
        'program_session_id' => $hierarchy['programSession']->id,
    ]);

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.presentations.edit', $presentation));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Presentations/Edit')
        ->has('presentation'));
});
