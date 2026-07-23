<?php

use App\Enums\EventPageKey;
use App\Models\Event;
use App\Models\EventPage;

it('stores event content pages when creating an event', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $response = $this->actingAs($user)->post(route('admin.events.store'), [
        'organization_id' => $organization->id,
        'title' => 'Kongre İçerik Testi',
        'description' => 'Açıklama',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
        'location' => 'İstanbul',
        'is_published' => false,
        'pages' => [
            'general_info' => '<p>Genel bilgiler içeriği</p>',
            'invitation' => '<p>Davet metni</p>',
            'abstract_submission' => '',
            'committees' => '',
            'registration_accommodation' => '',
            'contact' => '<p>İletişim bilgileri</p>',
        ],
    ]);

    $response->assertRedirect();

    $event = Event::query()->where('name', 'Kongre İçerik Testi')->first();

    expect($event)->not->toBeNull();

    $this->assertDatabaseHas('event_pages', [
        'event_id' => $event->id,
        'key' => EventPageKey::GeneralInfo->value,
        'content' => '<p>Genel bilgiler içeriği</p>',
    ]);

    $this->assertDatabaseHas('event_pages', [
        'event_id' => $event->id,
        'key' => EventPageKey::Contact->value,
        'content' => '<p>İletişim bilgileri</p>',
    ]);
});

it('updates event content pages when editing an event', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Güncellenecek Kongre',
    ]);

    EventPage::factory()->forKey(EventPageKey::Invitation)->create([
        'event_id' => $event->id,
        'content' => '<p>Eski davet</p>',
    ]);

    $response = $this->actingAs($user)->put(route('admin.events.update', $event), [
        'organization_id' => $organization->id,
        'title' => $event->name,
        'description' => 'Açıklama',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
        'location' => 'Ankara',
        'is_published' => false,
        'pages' => [
            'general_info' => '<p>Yeni genel bilgi</p>',
            'invitation' => '<p>Yeni davet metni</p>',
            'abstract_submission' => '',
            'committees' => '',
            'registration_accommodation' => '',
            'contact' => '',
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('event_pages', [
        'event_id' => $event->id,
        'key' => EventPageKey::Invitation->value,
        'content' => '<p>Yeni davet metni</p>',
    ]);

    $this->assertDatabaseHas('event_pages', [
        'event_id' => $event->id,
        'key' => EventPageKey::GeneralInfo->value,
        'content' => '<p>Yeni genel bilgi</p>',
    ]);
});

it('rejects invalid event content page keys', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.events.edit', $event))
        ->put(route('admin.events.update', $event), [
            'organization_id' => $organization->id,
            'title' => $event->name,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'pages' => [
                'invalid_key' => '<p>Geçersiz</p>',
            ],
        ]);

    $response->assertRedirect(route('admin.events.edit', $event));
    $response->assertSessionHasErrors(['pages']);
});

it('renders event edit page with page sections and pages map', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
    ]);

    EventPage::factory()->forKey(EventPageKey::Committees)->create([
        'event_id' => $event->id,
        'content' => '<p>Kurul listesi</p>',
    ]);

    $response = $this->actingAs($user)->get(route('admin.events.edit', $event));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Events/Edit')
        ->has('pageSections', count(EventPageKey::cases()))
        ->where('event.pages.committees', '<p>Kurul listesi</p>'));
});

it('renders event show page with pages and page sections', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
    ]);

    EventPage::factory()->forKey(EventPageKey::GeneralInfo)->create([
        'event_id' => $event->id,
        'content' => '<p>Genel bilgi</p>',
    ]);

    $response = $this->actingAs($user)->get(route('admin.events.show', $event));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Events/Show')
        ->has('pageSections', count(EventPageKey::cases()))
        ->where('event.pages.general_info', '<p>Genel bilgi</p>'));
});
