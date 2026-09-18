<?php

use App\Jobs\SendAnnouncementPush;
use App\Models\Announcement;
use App\Models\Event;
use App\Services\FcmSender;
use Illuminate\Support\Facades\Queue;

it('lets an admin create an announcement and dispatch push', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    Queue::fake();

    $this->actingAs($user)
        ->post(route('admin.events.announcements.store', $event), [
            'title' => 'Kahve arası uzadı',
            'body' => 'Fuaye 10 dakika daha açık.',
            'send_push' => true,
        ])
        ->assertRedirect(route('admin.events.announcements.index', $event));

    $announcement = Announcement::query()->where('event_id', $event->id)->first();
    expect($announcement)->not->toBeNull()
        ->and($announcement->title)->toBe('Kahve arası uzadı')
        ->and($announcement->published_at)->not->toBeNull();

    Queue::assertPushed(SendAnnouncementPush::class, fn (SendAnnouncementPush $job) => $job->announcementId === $announcement->id);
});

it('does not dispatch push when the checkbox is off', function () {
    ['organization' => $organization, 'user' => $user] = adminContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    Queue::fake();

    $this->actingAs($user)
        ->post(route('admin.events.announcements.store', $event), [
            'title' => 'Sessiz duyuru',
            'body' => 'Sadece listede görünsün.',
            'send_push' => false,
        ])
        ->assertRedirect();

    Queue::assertNothingPushed();
});

it('forbids editors from creating announcements', function () {
    ['organization' => $organization, 'user' => $user] = editorContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    $this->actingAs($user)
        ->post(route('admin.events.announcements.store', $event), [
            'title' => 'Yetkisiz',
            'body' => 'Gönderilemez',
        ])
        ->assertForbidden();
});

it('lets an organizer create announcements for their event', function () {
    ['organization' => $organization, 'user' => $user] = organizerContext();
    $event = Event::factory()->create(['organization_id' => $organization->id]);

    Queue::fake();

    $this->actingAs($user)
        ->post(route('admin.events.announcements.store', $event), [
            'title' => 'Organizatör duyurusu',
            'body' => 'Program güncellendi.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('announcements', [
        'event_id' => $event->id,
        'title' => 'Organizatör duyurusu',
    ]);
});

it('marks push_sent_at after the job runs with a configured sender', function () {
    $event = Event::factory()->create();
    $announcement = Announcement::factory()->create(['event_id' => $event->id]);

    $this->mock(FcmSender::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('sendToTokens')->once()->andReturn(['sent' => 1, 'failed' => 0]);
    });

    (new SendAnnouncementPush($announcement->id))->handle(app(FcmSender::class));

    expect($announcement->fresh()->push_sent_at)->not->toBeNull();
});
