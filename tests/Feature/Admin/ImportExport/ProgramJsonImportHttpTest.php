<?php

use App\Models\Event;
use App\Models\EventDay;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * @return array<int, array<string, mixed>>
 */
function programJsonFixture(): array
{
    $json = file_get_contents(base_path('tests/Feature/fixtures/program-import-sample.json'));

    return json_decode($json, true);
}

it('forbids users who cannot import program json', function () {
    $hierarchy = programHierarchy();
    $outsider = User::factory()->create(['role' => 'editor']);

    $this->actingAs($outsider)
        ->postJson(route('admin.events.import-program.preview', $hierarchy['event']), [
            'json' => json_encode(programJsonFixture()),
        ])
        ->assertForbidden();
});

it('previews program json without persisting records', function () {
    ['user' => $user, 'organization' => $organization] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-19',
    ]);

    $this->actingAs($user)
        ->postJson(route('admin.events.import-program.preview', $event), [
            'json' => json_encode(programJsonFixture()),
        ])
        ->assertOk()
        ->assertJsonPath('days', 1)
        ->assertJsonPath('venues', 1)
        ->assertJsonPath('sessions', 2)
        ->assertJsonPath('presentations', 1)
        ->assertJsonPath('participants', 2);

    $this->assertDatabaseCount('event_days', 0);
    $this->assertDatabaseCount('program_sessions', 0);
});

it('imports pasted program json and replaces existing program', function () {
    $hierarchy = programHierarchy();
    $hierarchy['event']->update([
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-19',
    ]);

    $this->actingAs($hierarchy['user'])
        ->postJson(route('admin.events.import-program', $hierarchy['event']), [
            'json' => json_encode(programJsonFixture()),
        ])
        ->assertOk()
        ->assertJsonPath('days', 1)
        ->assertJsonPath('sessions', 2)
        ->assertJsonPath('message', 'Program başarıyla içe aktarıldı.');

    $this->assertDatabaseMissing('program_sessions', ['title' => $hierarchy['programSession']->title]);
    $this->assertDatabaseHas('program_sessions', ['title' => 'AÇILIŞ OTURUMU']);
    $this->assertSame(1, EventDay::query()->where('event_id', $hierarchy['event']->id)->count());
});

it('imports program json from an uploaded file', function () {
    ['user' => $user, 'organization' => $organization] = adminContext();

    $event = Event::factory()->create([
        'organization_id' => $organization->id,
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-19',
    ]);

    $file = UploadedFile::fake()->createWithContent(
        'program.json',
        json_encode(programJsonFixture())
    );

    $this->actingAs($user)
        ->post(route('admin.events.import-program', $event), [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ])
        ->assertOk()
        ->assertJsonPath('days', 1)
        ->assertJsonPath('presentations', 1);

    $this->assertDatabaseHas('venues', ['display_name' => 'Test Salonu']);
});

it('rejects invalid json', function () {
    ['user' => $user, 'event' => $event] = programHierarchy();

    $this->actingAs($user)
        ->postJson(route('admin.events.import-program.preview', $event), [
            'json' => '{not-json',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('json');
});

it('rejects an empty program json array', function () {
    ['user' => $user, 'event' => $event] = programHierarchy();

    $this->actingAs($user)
        ->postJson(route('admin.events.import-program.preview', $event), [
            'json' => '[]',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('json');
});

it('rejects timeline json format', function () {
    ['user' => $user, 'event' => $event] = programHierarchy();

    $this->actingAs($user)
        ->postJson(route('admin.events.import-program.preview', $event), [
            'json' => json_encode([
                'event' => ['id' => $event->id, 'name' => $event->name],
                'generated_at' => now()->toISOString(),
                'data' => [],
            ]),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('json');
});

it('exports program json for download', function () {
    $hierarchy = programHierarchy();

    $this->actingAs($hierarchy['user'])
        ->get(route('admin.export.events.program-json', $hierarchy['event']))
        ->assertOk()
        ->assertHeader('content-disposition')
        ->assertJsonPath('0.Date', $hierarchy['eventDay']->date->format('d.m.Y'))
        ->assertJsonPath('0.Venues.0.Sessions.0.Session', $hierarchy['programSession']->title);
});
