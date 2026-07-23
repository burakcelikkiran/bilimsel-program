<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Venue;
use App\Services\ProgramJsonExporter;
use App\Services\ProgramJsonImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramJsonImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFixture(): array
    {
        $json = file_get_contents(base_path('tests/Feature/fixtures/program-import-sample.json'));

        return json_decode($json, true);
    }

    public function test_program_json_importer_creates_hierarchy(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'import-test-event',
        ]);

        $result = app(ProgramJsonImporter::class)->import($event, $this->loadFixture());

        $this->assertSame(1, $result->days);
        $this->assertSame(1, $result->venues);
        $this->assertSame(2, $result->sessions);
        $this->assertSame(1, $result->presentations);
        $this->assertSame(2, $result->participants);
        $this->assertSame(1, $result->moderatorLinks);
        $this->assertSame(1, $result->speakerLinks);

        $this->assertDatabaseCount('event_days', 1);
        $this->assertDatabaseCount('venues', 1);
        $this->assertDatabaseCount('program_sessions', 2);
        $this->assertDatabaseCount('presentations', 1);
        $this->assertDatabaseCount('participants', 2);

        $session = ProgramSession::query()->where('title', 'ACİL SERVİSTE SIK GÖRÜLEN ALERJİK HASTALIKLAR')->first();
        $this->assertNotNull($session);
        $this->assertSame('main', $session->session_type);
        $this->assertSame('Oturum Başkanları', $session->moderator_title);
        $this->assertCount(1, $session->moderators);

        $openingSession = ProgramSession::query()->where('title', 'AÇILIŞ OTURUMU')->first();
        $this->assertNotNull($openingSession);
        $this->assertSame('special', $openingSession->session_type);
        $this->assertTrue($openingSession->is_break);

        $presentation = Presentation::query()->where('title', 'Çocuklarda Ne Zaman İlaç Alerjisi Düşünelim?')->first();
        $this->assertNotNull($presentation);
        $this->assertCount(1, $presentation->speakers);
    }

    public function test_dry_run_does_not_persist_records(): void
    {
        $event = Event::factory()->create(['slug' => 'dry-run-event']);

        $result = app(ProgramJsonImporter::class)->import($event, $this->loadFixture(), dryRun: true);

        $this->assertSame(1, $result->days);
        $this->assertSame(2, $result->sessions);
        $this->assertDatabaseCount('event_days', 0);
        $this->assertDatabaseCount('program_sessions', 0);
    }

    public function test_fresh_import_replaces_existing_program(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);

        $eventDay = EventDay::factory()->create(['event_id' => $event->id]);
        $venue = Venue::factory()->create(['event_day_id' => $eventDay->id]);
        ProgramSession::factory()->create(['venue_id' => $venue->id, 'title' => 'Eski Oturum']);

        app(ProgramJsonImporter::class)->import($event, $this->loadFixture(), fresh: true);

        $this->assertDatabaseMissing('program_sessions', ['title' => 'Eski Oturum']);
        $this->assertDatabaseHas('program_sessions', ['title' => 'AÇILIŞ OTURUMU']);
        $this->assertSame(1, EventDay::query()->where('event_id', $event->id)->count());
    }

    public function test_import_export_round_trip_preserves_structure(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'round-trip-event',
        ]);

        app(ProgramJsonImporter::class)->import($event, $this->loadFixture());

        $exported = app(ProgramJsonExporter::class)->export($event->fresh());

        $this->assertCount(1, $exported);
        $this->assertSame('16.04.2026', $exported[0]['Date']);
        $this->assertSame('Test Salonu', $exported[0]['Venues'][0]['Venue']);
        $this->assertSame('AÇILIŞ OTURUMU', $exported[0]['Venues'][0]['Sessions'][0]['Session']);
        $this->assertSame('Özel Oturum', $exported[0]['Venues'][0]['Sessions'][0]['SessionType']);
        $this->assertFalse($exported[0]['Venues'][0]['Sessions'][0]['ShowTime']);

        $mainSession = $exported[0]['Venues'][0]['Sessions'][1];
        $this->assertSame('Oturum', $mainSession['SessionType']);
        $this->assertSame('Oturum Başkanları', $mainSession['StaffList'][0]['StaffType']);
        $this->assertSame('Haluk Çokuğraş', $mainSession['StaffList'][0]['Staff'][0]['FullName']);
        $this->assertSame('Çocuklarda Ne Zaman İlaç Alerjisi Düşünelim?', $mainSession['SessionContents'][0]['SessionContent']);
    }

    public function test_public_api_returns_imported_program_json(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->published()->create([
            'organization_id' => $organization->id,
            'slug' => 'public-import-event',
        ]);

        app(ProgramJsonImporter::class)->import($event, $this->loadFixture());

        $this->getJson('/api/v1/events/public-import-event/program.json')
            ->assertOk()
            ->assertJsonPath('0.Venues.0.Sessions.1.Session', 'ACİL SERVİSTE SIK GÖRÜLEN ALERJİK HASTALIKLAR')
            ->assertJsonPath('0.Venues.0.Sessions.0.SessionType', 'Özel Oturum');
    }

    public function test_participants_are_deduplicated_by_name(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);

        $fixture = $this->loadFixture();
        $fixture[0]['Venues'][0]['Sessions'][1]['StaffList'][0]['Staff'][] = [
            'Title' => 'Prof. Dr.',
            'FullName' => 'Haluk Çokuğraş',
            'Institution' => 'Test Üniversitesi',
        ];

        app(ProgramJsonImporter::class)->import($event, $fixture);

        $this->assertSame(2, Participant::query()->where('organization_id', $organization->id)->count());
    }
}
