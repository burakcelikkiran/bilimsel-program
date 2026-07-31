<?php

use App\Models\Organization;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;

it('stores a program session with valid data', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.program-sessions.store'), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Yeni Oturum',
        'description' => 'Oturum açıklaması',
        'start_time' => '14:00',
        'end_time' => '15:00',
        'session_type' => 'main',
        'moderator_title' => 'Oturum Başkanı',
        'is_break' => false,
        'moderator_ids' => [],
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('program_sessions', [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Yeni Oturum',
        'start_time' => '14:00:00',
    ]);
});

it('updates a program session with valid data', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $response = $this->actingAs($hierarchy['user'])->put(route('admin.program-sessions.update', $session), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Güncel Oturum',
        'description' => 'Güncel açıklama',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'session_type' => 'main',
        'moderator_title' => 'Başkan',
        'is_break' => false,
        'moderator_ids' => [],
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('program_sessions', [
        'id' => $session->id,
        'title' => 'Güncel Oturum',
    ]);
});

it('returns validation errors when storing program session without title', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.create'))
        ->post(route('admin.program-sessions.store'), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => '',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'session_type' => 'main',
        ]);

    $response->assertRedirect(route('admin.program-sessions.create'));
    $response->assertSessionHasErrors(['title']);
});

it('returns validation errors when updating program session with end time before start time', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.edit', $session))
        ->put(route('admin.program-sessions.update', $session), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => $session->title,
            'start_time' => '15:00',
            'end_time' => '14:00',
            'session_type' => 'main',
        ]);

    $response->assertRedirect(route('admin.program-sessions.edit', $session));
    $response->assertSessionHasErrors(['end_time']);
});

it('renders program session edit page via inertia', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.program-sessions.edit', $hierarchy['programSession']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Edit')
        ->has('programSession'));
});

it('renders program session create page with venue context preselected', function () {
    $hierarchy = programHierarchy();

    $response = $this->actingAs($hierarchy['user'])->get(
        route('admin.program-sessions.create', ['venue_id' => $hierarchy['venue']->id])
    );

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Create')
        ->where('selectedEventId', $hierarchy['event']->id)
        ->where('selectedEventDayId', $hierarchy['eventDay']->id)
        ->where('selectedVenueId', (string) $hierarchy['venue']->id)
        ->has('eventDays', 1)
        ->has('venues', 1)
        ->has('selectedEvent')
        ->has('selectedEventDay'));
});

it('lists all organization participants on create when event is selected', function () {
    $hierarchy = programHierarchy();

    $moderator = Participant::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
        'first_name' => 'Moderator',
        'is_moderator' => true,
        'is_speaker' => false,
    ]);

    $speakerOnly = Participant::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
        'first_name' => 'SpeakerOnly',
        'is_moderator' => false,
        'is_speaker' => true,
    ]);

    $otherOrgParticipant = Participant::factory()->create([
        'organization_id' => Organization::factory()->create()->id,
        'first_name' => 'OtherOrg',
    ]);

    $response = $this->actingAs($hierarchy['user'])->get(
        route('admin.program-sessions.create', ['event_id' => $hierarchy['event']->id])
    );

    $response->assertOk();
    $response->assertInertia(function ($page) use ($moderator, $speakerOnly, $otherOrgParticipant) {
        $page->component('Admin/ProgramSessions/Create')
            ->has('participants', 2);

        $names = collect($page->toArray()['props']['participants'])->pluck('full_name');

        expect($names->all())->toContain($moderator->full_name)
            ->toContain($speakerOnly->full_name)
            ->not->toContain($otherOrgParticipant->full_name);
    });
});

it('returns empty participants on create when no event is selected', function () {
    $hierarchy = programHierarchy();

    Participant::factory()->count(3)->create([
        'organization_id' => $hierarchy['organization']->id,
    ]);

    $response = $this->actingAs($hierarchy['user'])->get(route('admin.program-sessions.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ProgramSessions/Create')
        ->has('participants', 0));
});

it('stores a program session with non-moderator participants', function () {
    $hierarchy = programHierarchy();

    $participants = Participant::factory()->count(2)->create([
        'organization_id' => $hierarchy['organization']->id,
        'is_moderator' => false,
        'is_speaker' => true,
    ]);

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.program-sessions.store'), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Katılımcılı Oturum',
        'description' => 'Oturum açıklaması',
        'start_time' => '14:00',
        'end_time' => '15:00',
        'session_type' => 'main',
        'moderator_title' => 'Oturum Başkanı',
        'is_break' => false,
        'moderator_ids' => $participants->pluck('id')->all(),
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $session = ProgramSession::query()
        ->where('title', 'Katılımcılı Oturum')
        ->firstOrFail();

    expect($session->moderators()->pluck('participants.id')->all())
        ->toEqual($participants->pluck('id')->all());
});

it('stores a program session with more than five participants', function () {
    $hierarchy = programHierarchy();

    $participants = Participant::factory()->count(6)->create([
        'organization_id' => $hierarchy['organization']->id,
        'is_moderator' => false,
    ]);

    $response = $this->actingAs($hierarchy['user'])->post(route('admin.program-sessions.store'), [
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'Geniş Oturum',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'session_type' => 'main',
        'moderator_title' => 'Oturum Başkanları',
        'is_break' => false,
        'moderator_ids' => $participants->pluck('id')->all(),
        'category_ids' => [],
    ]);

    $response->assertRedirect();

    $session = ProgramSession::query()
        ->where('title', 'Geniş Oturum')
        ->firstOrFail();

    expect($session->moderators()->count())->toBe(6);
});

it('rejects participants from a different organization when storing a session', function () {
    $hierarchy = programHierarchy();

    $foreignParticipant = Participant::factory()->create([
        'organization_id' => Organization::factory()->create()->id,
    ]);

    $response = $this->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.create'))
        ->post(route('admin.program-sessions.store'), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => 'Geçersiz Katılımcılı Oturum',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'session_type' => 'main',
            'moderator_title' => 'Oturum Başkanı',
            'is_break' => false,
            'moderator_ids' => [$foreignParticipant->id],
            'category_ids' => [],
        ]);

    $response->assertRedirect(route('admin.program-sessions.create'));
    $response->assertSessionHasErrors(['moderator_ids.0']);
});

it('allows the same participant to moderate a session and speak in a presentation', function () {
    $hierarchy = programHierarchy();
    $session = $hierarchy['programSession'];

    $participant = Participant::factory()->create([
        'organization_id' => $hierarchy['organization']->id,
        'is_moderator' => false,
        'is_speaker' => false,
    ]);

    $session->moderators()->attach($participant->id, ['sort_order' => 1]);

    $presentation = Presentation::factory()->create([
        'program_session_id' => $session->id,
        'start_time' => '10:05',
        'end_time' => '10:25',
    ]);

    $presentation->speakers()->attach($participant->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    expect($session->moderators()->where('participants.id', $participant->id)->exists())->toBeTrue();
    expect($presentation->speakers()->where('participants.id', $participant->id)->exists())->toBeTrue();
});
