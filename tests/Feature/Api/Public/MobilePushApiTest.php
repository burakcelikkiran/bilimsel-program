<?php

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventDevice;

it('registers a device token for the default tpk event', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'turkpediatri-kongresi-2026',
    ]);

    $this->postJson('/api/v1/devices/register', [
        'token' => 'fcm-token-abc',
        'platform' => 'android',
        'app' => 'tpk2026',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.event', $event->slug);

    $this->assertDatabaseHas('event_devices', [
        'event_id' => $event->id,
        'token' => 'fcm-token-abc',
        'platform' => 'android',
        'app' => 'tpk2026',
    ]);
});

it('updates last_seen_at when the same token registers again', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'turkpediatri-kongresi-2026',
    ]);

    $device = EventDevice::factory()->create([
        'event_id' => $event->id,
        'token' => 'same-token',
        'platform' => 'ios',
        'last_seen_at' => now()->subDay(),
    ]);

    $this->postJson('/api/v1/devices/register', [
        'token' => 'same-token',
        'platform' => 'android',
        'app' => 'tpk2026',
    ])->assertOk();

    expect($device->fresh()->platform)->toBe('android')
        ->and($device->fresh()->last_seen_at->isAfter(now()->subMinute()))->toBeTrue();
});

it('rejects invalid platform values', function () {
    Event::factory()->published()->create([
        'slug' => 'turkpediatri-kongresi-2026',
    ]);

    $this->postJson('/api/v1/devices/register', [
        'token' => 'token',
        'platform' => 'web',
    ])->assertUnprocessable();
});

it('returns published announcements in the mobile payload shape', function () {
    $event = Event::factory()->published()->create(['slug' => 'duyuru-event']);
    $published = Announcement::factory()->create([
        'event_id' => $event->id,
        'title' => 'Salon değişikliği',
        'body' => 'Ana salon 2. kata taşındı.',
        'published_at' => now()->subHour(),
    ]);
    Announcement::factory()->unpublished()->create([
        'event_id' => $event->id,
        'title' => 'Taslak',
    ]);

    $this->getJson("/api/v1/events/{$event->slug}/announcements.json")
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', (string) $published->id)
        ->assertJsonPath('0.Baslik', 'Salon değişikliği')
        ->assertJsonPath('0.Mesaj', 'Ana salon 2. kata taşındı.')
        ->assertJsonPath('0.OturumID', null);
});

it('hides announcements json for unpublished events', function () {
    $event = Event::factory()->create([
        'is_published' => false,
        'slug' => 'gizli-duyuru',
    ]);
    Announcement::factory()->create(['event_id' => $event->id]);

    $this->getJson("/api/v1/events/{$event->slug}/announcements.json")
        ->assertNotFound();
});
