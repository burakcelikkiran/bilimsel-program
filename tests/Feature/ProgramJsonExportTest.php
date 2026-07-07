<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\User;
use App\Models\Venue;
use App\Services\ProgramJsonExporter;
use App\Support\ProgramSessionTypeMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramJsonExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_json_exporter_maps_event_hierarchy(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->published()->create([
            'organization_id' => $organization->id,
            'slug' => 'test-kongresi-export',
        ]);

        $eventDay = EventDay::factory()->create([
            'event_id' => $event->id,
            'date' => '2026-04-16',
            'display_name' => '1. Gün',
            'is_active' => true,
        ]);

        $venue = Venue::factory()->create([
            'event_day_id' => $eventDay->id,
            'display_name' => 'Ana Salon',
            'sort_order' => 1,
        ]);

        $moderator = Participant::factory()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Haluk',
            'last_name' => 'Çokuğraş',
            'title' => 'Prof. Dr.',
            'affiliation' => 'Test Üniversitesi',
            'is_moderator' => true,
        ]);

        $speaker = Participant::factory()->speaker()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Emine',
            'last_name' => 'Dibek Mısırlıoğlu',
            'affiliation' => 'Örnek Hastane',
        ]);

        $session = ProgramSession::factory()->create([
            'venue_id' => $venue->id,
            'title' => 'ACİL SERVİSTE SIK GÖRÜLEN ALERJİK HASTALIKLAR',
            'description' => 'Oturum konusu',
            'start_time' => '13:30',
            'end_time' => '14:30',
            'session_type' => 'main',
            'moderator_title' => 'Oturum Başkanları',
            'is_break' => false,
        ]);

        $session->moderators()->attach($moderator->id, ['sort_order' => 1]);

        $presentation = Presentation::create([
            'program_session_id' => $session->id,
            'title' => 'Çocuklarda Ne Zaman İlaç Alerjisi Düşünelim?',
            'abstract' => 'Ek bilgi metni',
            'start_time' => '13:30',
            'end_time' => '13:45',
            'presentation_type' => 'oral',
            'sort_order' => 1,
        ]);

        $presentation->speakers()->attach($speaker->id, [
            'speaker_role' => 'primary',
            'sort_order' => 1,
        ]);

        $data = app(ProgramJsonExporter::class)->export($event->fresh());

        $this->assertCount(1, $data);
        $this->assertSame('16.04.2026', $data[0]['Tarih']);
        $this->assertSame('2026-04-16', $data[0]['IsoTarih']);
        $this->assertSame('Ana Salon', $data[0]['Salonlar'][0]['Salon']);

        $oturum = $data[0]['Salonlar'][0]['Oturumlar'][0];
        $this->assertSame('Oturum', $oturum['OturumTipi']);
        $this->assertSame('ACİL SERVİSTE SIK GÖRÜLEN ALERJİK HASTALIKLAR', $oturum['Oturum']);
        $this->assertSame('13:30', $oturum['BaslangicSaati']);
        $this->assertSame('14:30', $oturum['BitisSaati']);
        $this->assertSame('"2026-04-16T13:30:00"', $oturum['BaslangicTarihiJSON']);
        $this->assertTrue($oturum['SaatGosterim']);
        $this->assertFalse($oturum['LogoDurum']);
        $this->assertSame(
            ProgramSessionTypeMapper::sessionUuid($session->id),
            $oturum['OturumID']
        );

        $this->assertSame('Oturum Başkanları', $oturum['GorevliListesi'][0]['GorevliTipi']);
        $this->assertSame('Prof. Dr.', $oturum['GorevliListesi'][0]['Gorevliler'][0]['Unvan']);
        $this->assertSame('Haluk Çokuğraş', $oturum['GorevliListesi'][0]['Gorevliler'][0]['AdSoyad']);
        $this->assertSame('Test Üniversitesi', $oturum['GorevliListesi'][0]['Gorevliler'][0]['Kurum']);

        $icerik = $oturum['OturumIcerikBilgileri'][0];
        $this->assertSame('Çocuklarda Ne Zaman İlaç Alerjisi Düşünelim?', $icerik['OturumIcerik']);
        $this->assertSame('Ek bilgi metni', $icerik['ExtraBilgi']);
        $this->assertSame('Konuşmacı', $icerik['GorevliListesi'][0]['GorevliTipi']);
        $this->assertSame('Emine Dibek Mısırlıoğlu', $icerik['GorevliListesi'][0]['Gorevliler'][0]['AdSoyad']);
    }

    public function test_public_api_returns_program_json_format(): void
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->published()->create([
            'organization_id' => $organization->id,
            'slug' => 'public-program-json',
        ]);

        $eventDay = EventDay::factory()->create([
            'event_id' => $event->id,
            'date' => '2026-04-15',
            'is_active' => true,
        ]);

        $venue = Venue::factory()->create([
            'event_day_id' => $eventDay->id,
            'display_name' => 'Workshop Salonu',
        ]);

        ProgramSession::factory()->create([
            'venue_id' => $venue->id,
            'title' => 'Test Oturumu',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'session_type' => 'workshop',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->slug.'/program.json');

        $response->assertOk()
            ->assertJsonPath('0.Tarih', '15.04.2026')
            ->assertJsonPath('0.IsoTarih', '2026-04-15')
            ->assertJsonPath('0.Salonlar.0.Salon', 'Workshop Salonu')
            ->assertJsonPath('0.Salonlar.0.Oturumlar.0.OturumTipi', 'kurs')
            ->assertJsonPath('0.Salonlar.0.Oturumlar.0.Oturum', 'Test Oturumu');
    }

    public function test_unpublished_event_program_json_returns_404_for_guests(): void
    {
        $event = Event::factory()->create([
            'slug' => 'gizli-program-json',
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/events/'.$event->slug.'/program.json')
            ->assertNotFound();
    }

    public function test_authenticated_admin_can_export_unpublished_event_program_json(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'admin']);

        $event = Event::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'taslak-program-json',
            'is_published' => false,
        ]);

        EventDay::factory()->create([
            'event_id' => $event->id,
            'date' => '2026-06-01',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/events/'.$event->slug.'/program.json')
            ->assertOk()
            ->assertJsonPath('0.Tarih', '01.06.2026');
    }

    public function test_admin_can_export_program_json_from_timeline(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'admin']);

        $event = Event::factory()->create([
            'organization_id' => $organization->id,
            'slug' => 'admin-program-json',
        ]);

        $eventDay = EventDay::factory()->create([
            'event_id' => $event->id,
            'date' => '2026-05-01',
            'is_active' => true,
        ]);

        $venue = Venue::factory()->create([
            'event_day_id' => $eventDay->id,
            'display_name' => 'Salon A',
        ]);

        ProgramSession::factory()->create([
            'venue_id' => $venue->id,
            'title' => 'Admin Export Oturumu',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'session_type' => 'break',
            'is_break' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.timeline.export', $event), [
                'format' => 'program_json',
            ]);

        $response->assertOk()
            ->assertHeader('content-disposition')
            ->assertJsonPath('0.Tarih', '01.05.2026')
            ->assertJsonPath('0.Salonlar.0.Oturumlar.0.OturumTipi', 'Ara')
            ->assertJsonPath('0.Salonlar.0.Oturumlar.0.SaatGosterim', false);
    }
}
