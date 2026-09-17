<?php

use App\Models\Organization;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\Sponsor;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

it('destroys and duplicates a participant', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $participant = Participant::factory()->create([
        'organization_id' => $org->id,
        'email' => 'konusmaci@example.com',
    ]);

    test()->actingAs($user)
        ->post(route('admin.participants.duplicate', $participant))
        ->assertRedirect();

    expect(Participant::query()->where('organization_id', $org->id)->count())->toBe(2)
        ->and(Participant::query()->where('last_name', 'like', '%Kopya%')->exists())->toBeTrue();

    test()->actingAs($user)
        ->delete(route('admin.participants.destroy', $participant))
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::find($participant->id))->toBeNull();
});

it('bulk destroys participants who have presentations and moderated sessions', function () {
    $data = fullEventProgram();

    $data['presentation']->speakers()->attach($data['participant']->id, [
        'speaker_role' => 'primary',
        'sort_order' => 1,
    ]);

    $data['programSession']->moderators()->attach($data['participant']->id, [
        'sort_order' => 1,
    ]);

    test()->actingAs($data['user'])
        ->delete(route('admin.participants.bulk-destroy'), [
            'participant_ids' => [$data['participant']->id],
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::find($data['participant']->id))->toBeNull()
        ->and(Presentation::find($data['presentation']->id))->not->toBeNull()
        ->and(DB::table('presentation_speakers')->where('participant_id', $data['participant']->id)->count())->toBe(0)
        ->and(DB::table('program_session_moderators')->where('participant_id', $data['participant']->id)->count())->toBe(0);
});

it('bulk destroys and bulk duplicates participants', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $first = Participant::factory()->create(['organization_id' => $org->id]);
    $second = Participant::factory()->create(['organization_id' => $org->id]);

    test()->actingAs($user)
        ->post(route('admin.participants.bulk-duplicate'), [
            'participant_ids' => [$first->id],
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::query()->where('organization_id', $org->id)->count())->toBe(3);

    test()->actingAs($user)
        ->delete(route('admin.participants.bulk-destroy'), [
            'participant_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect(route('admin.participants.index'))
        ->assertSessionHas('success');

    expect(Participant::find($first->id))->toBeNull()
        ->and(Participant::find($second->id))->toBeNull();
});

it('destroys and duplicates a presentation', function () {
    $data = fullEventProgram();

    test()->actingAs($data['user'])
        ->post(route('admin.presentations.duplicate', $data['presentation']))
        ->assertRedirect();

    expect(Presentation::query()->where('program_session_id', $data['programSession']->id)->count())->toBe(2);

    test()->actingAs($data['user'])
        ->delete(route('admin.presentations.destroy', $data['presentation']))
        ->assertRedirect(route('admin.presentations.index'))
        ->assertSessionHas('success');

    expect(Presentation::find($data['presentation']->id))->toBeNull();
});

it('bulk destroys and bulk duplicates presentations', function () {
    $data = fullEventProgram();
    $second = Presentation::factory()->create([
        'program_session_id' => $data['programSession']->id,
        'start_time' => '10:45',
        'end_time' => '11:00',
    ]);

    test()->actingAs($data['user'])
        ->post(route('admin.presentations.bulk-duplicate'), [
            'presentation_ids' => [$data['presentation']->id],
        ])
        ->assertRedirect(route('admin.presentations.index'))
        ->assertSessionHas('success');

    test()->actingAs($data['user'])
        ->delete(route('admin.presentations.bulk-destroy'), [
            'presentation_ids' => [$data['presentation']->id, $second->id],
        ])
        ->assertRedirect(route('admin.presentations.index'))
        ->assertSessionHas('success');

    expect(Presentation::find($data['presentation']->id))->toBeNull()
        ->and(Presentation::find($second->id))->toBeNull();
});

it('destroys and duplicates a sponsor', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $sponsor = Sponsor::factory()->create(['organization_id' => $org->id]);

    test()->actingAs($user)
        ->post(route('admin.sponsors.duplicate', $sponsor))
        ->assertRedirect();

    expect(Sponsor::query()->where('organization_id', $org->id)->count())->toBe(2);

    test()->actingAs($user)
        ->delete(route('admin.sponsors.destroy', $sponsor))
        ->assertRedirect(route('admin.sponsors.index'))
        ->assertSessionHas('success');

    expect(Sponsor::find($sponsor->id))->toBeNull();
});

it('bulk destroys and bulk duplicates sponsors', function () {
    ['organization' => $org, 'user' => $user] = adminContext();
    $first = Sponsor::factory()->create(['organization_id' => $org->id]);
    $second = Sponsor::factory()->create(['organization_id' => $org->id]);

    test()->actingAs($user)
        ->post(route('admin.sponsors.bulk-duplicate'), [
            'sponsor_ids' => [$first->id],
        ])
        ->assertRedirect(route('admin.sponsors.index'))
        ->assertSessionHas('success');

    test()->actingAs($user)
        ->delete(route('admin.sponsors.bulk-destroy'), [
            'sponsor_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect(route('admin.sponsors.index'))
        ->assertSessionHas('success');

    expect(Sponsor::find($first->id))->toBeNull()
        ->and(Sponsor::find($second->id))->toBeNull();
});

it('destroys, duplicates and toggles an organization', function () {
    ['user' => $user] = adminContext();
    $organization = Organization::factory()->create(['is_active' => true]);

    test()->actingAs($user)
        ->post(route('admin.organizations.duplicate', $organization))
        ->assertRedirect();

    expect(Organization::query()->where('name', 'like', '%Kopya%')->exists())->toBeTrue();

    test()->actingAs($user)
        ->patch(route('admin.organizations.toggle-status', $organization))
        ->assertRedirect();

    expect($organization->fresh()->is_active)->toBeFalse();

    test()->actingAs($user)
        ->delete(route('admin.organizations.destroy', $organization))
        ->assertRedirect(route('admin.organizations.index'))
        ->assertSessionHas('success');

    expect(Organization::find($organization->id))->toBeNull();
});

it('bulk destroys and bulk toggles organizations', function () {
    ['user' => $user] = adminContext();
    $first = Organization::factory()->create(['is_active' => true]);
    $second = Organization::factory()->create(['is_active' => true]);

    test()->actingAs($user)
        ->patch(route('admin.organizations.bulk-toggle-status'), [
            'organization_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect(route('admin.organizations.index'))
        ->assertSessionHas('success');

    expect($first->fresh()->is_active)->toBeFalse()
        ->and($second->fresh()->is_active)->toBeFalse();

    test()->actingAs($user)
        ->delete(route('admin.organizations.bulk-destroy'), [
            'organization_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect(route('admin.organizations.index'))
        ->assertSessionHas('success');

    expect(Organization::find($first->id))->toBeNull()
        ->and(Organization::find($second->id))->toBeNull();
});

it('destroys and duplicates a venue without sessions', function () {
    $data = programHierarchy();
    $emptyVenue = Venue::factory()->create([
        'event_day_id' => $data['eventDay']->id,
    ]);

    test()->actingAs($data['user'])
        ->post(route('admin.venues.duplicate', $emptyVenue))
        ->assertRedirect();

    expect(Venue::query()->where('event_day_id', $data['eventDay']->id)->where('name', 'like', '%Kopya%')->exists())->toBeTrue();

    test()->actingAs($data['user'])
        ->delete(route('admin.venues.destroy', $emptyVenue))
        ->assertRedirect(route('admin.venues.index'))
        ->assertSessionHas('success');

    expect(Venue::find($emptyVenue->id))->toBeNull();
});

it('bulk destroys empty venues and bulk duplicates venues', function () {
    $data = programHierarchy();
    $emptyVenue = Venue::factory()->create([
        'event_day_id' => $data['eventDay']->id,
    ]);

    test()->actingAs($data['user'])
        ->post(route('admin.venues.bulk-duplicate'), [
            'venue_ids' => [$emptyVenue->id],
        ])
        ->assertRedirect(route('admin.venues.index'))
        ->assertSessionHas('success');

    test()->actingAs($data['user'])
        ->delete(route('admin.venues.bulk-destroy'), [
            'venue_ids' => [$emptyVenue->id],
        ])
        ->assertRedirect(route('admin.venues.index'))
        ->assertSessionHas('success');

    expect(Venue::find($emptyVenue->id))->toBeNull();
});
