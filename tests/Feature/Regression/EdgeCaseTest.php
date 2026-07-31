<?php

use App\Models\Event;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Sponsor;

it('prevents duplicate participant email within organization on store', function () {
    ['organization' => $org, 'user' => $user] = adminContext();

    Participant::factory()->create([
        'organization_id' => $org->id,
        'email' => 'duplicate@example.com',
    ]);

    test()->actingAs($user)
        ->from(route('admin.participants.create'))
        ->post(route('admin.participants.store'), [
            'organization_id' => $org->id,
            'first_name' => 'Yeni',
            'last_name' => 'Kişi',
            'email' => 'duplicate@example.com',
            'is_speaker' => false,
            'is_moderator' => false,
        ])
        ->assertSessionHasErrors(['email']);
});

it('prevents storing overlapping program sessions in same venue', function () {
    $hierarchy = programHierarchy();

    test()->actingAs($hierarchy['user'])
        ->from(route('admin.program-sessions.create'))
        ->post(route('admin.program-sessions.store'), [
            'venue_id' => $hierarchy['venue']->id,
            'title' => 'Çakışan Oturum',
            'start_time' => '10:15',
            'end_time' => '10:45',
            'session_type' => 'main',
            'is_break' => false,
        ])
        ->assertSessionHasErrors(['start_time']);
});

it('soft deletes sponsor record', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $sponsor = Sponsor::factory()->create(['organization_id' => $org->id]);

    test()->actingAs($user)
        ->delete(route('admin.sponsors.destroy', $sponsor))
        ->assertRedirect();

    expect(Sponsor::withTrashed()->find($sponsor->id)->trashed())->toBeTrue();
});

it('prevents deleting published event via policy', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $event = Event::factory()->published()->create(['organization_id' => $org->id]);

    test()->actingAs($user)
        ->delete(route('admin.events.destroy', $event))
        ->assertForbidden();

    expect(Event::find($event->id))->not->toBeNull();
});

it('deletes program session with presentations and detaches participant relations', function () {
    $data = fullEventProgram();

    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    $data['programSession']->moderators()->attach($data['participant']->id, [
        'sort_order' => 1,
    ]);

    test()->actingAs($data['user'])
        ->delete(route('admin.program-sessions.destroy', $data['programSession']))
        ->assertRedirect(route('admin.program-sessions.index'))
        ->assertSessionHas('success');

    expect(ProgramSession::find($data['programSession']->id))->toBeNull()
        ->and(Presentation::find($data['presentation']->id))->toBeNull()
        ->and(Participant::find($data['participant']->id))->not->toBeNull()
        ->and(DB::table('presentation_speakers')->where('presentation_id', $data['presentation']->id)->count())->toBe(0)
        ->and(DB::table('program_session_moderators')->where('program_session_id', $data['programSession']->id)->count())->toBe(0);
});
