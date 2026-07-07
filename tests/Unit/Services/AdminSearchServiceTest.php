<?php

use App\Models\Event;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\AdminSearchService;

it('returns empty array for short search queries', function () {
    $user = User::factory()->admin()->create();
    $service = new AdminSearchService;

    expect($service->search($user, 'a'))->toBe([]);
});

it('finds events participants sessions and sponsors for admin', function () {
    $organization = Organization::factory()->create(['name' => 'UniqueOrgSearch']);
    $user = User::factory()->admin()->create();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'UniqueSearchEvent',
    ]);

    Participant::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'UniqueSearch',
        'last_name' => 'Participant',
    ]);

    $hierarchy = programHierarchy($organization, $user);
    ProgramSession::factory()->create([
        'venue_id' => $hierarchy['venue']->id,
        'title' => 'UniqueSearchSession',
    ]);

    Sponsor::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'UniqueSearchSponsor',
    ]);

    $results = (new AdminSearchService)->search($user, 'UniqueSearch');
    $types = collect($results)->pluck('type')->unique()->values()->all();

    expect($types)->toContain('events', 'participants', 'sessions', 'sponsors');
    expect(collect($results)->firstWhere('type', 'events')['id'])->toBe($event->id);
});

it('scopes search results to editor organizations', function () {
    ['organization' => $org, 'user' => $editor] = editorContext();
    $otherOrg = Organization::factory()->create();

    Event::factory()->create([
        'organization_id' => $org->id,
        'name' => 'EditorVisibleEvent',
    ]);

    Event::factory()->create([
        'organization_id' => $otherOrg->id,
        'name' => 'EditorHiddenEvent',
    ]);

    $results = (new AdminSearchService)->search($editor, 'Editor');
    $titles = collect($results)->where('type', 'events')->pluck('title')->all();

    expect($titles)->toContain('EditorVisibleEvent');
    expect($titles)->not->toContain('EditorHiddenEvent');
});
