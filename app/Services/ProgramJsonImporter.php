<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Venue;
use App\Support\ProgramSessionTypeMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProgramJsonImporter
{
    /** @var array<string, Participant> */
    private array $participantCache = [];

    private ProgramJsonImportResult $result;

    public function __construct()
    {
        $this->result = new ProgramJsonImportResult;
    }

    /**
     * @param  array<int, array<string, mixed>>  $programData
     */
    public function import(Event $event, array $programData, bool $dryRun = false, bool $fresh = false): ProgramJsonImportResult
    {
        $this->result = new ProgramJsonImportResult;
        $this->participantCache = [];

        if ($fresh && ! $dryRun) {
            $this->clearEventProgram($event);
        }

        if ($dryRun) {
            return $this->countProgramData($event, $programData);
        }

        DB::transaction(function () use ($event, $programData) {
            $this->collectParticipants($event, $programData);
            $this->importProgramHierarchy($event, $programData);
        });

        return $this->result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $programData
     */
    private function countProgramData(Event $event, array $programData): ProgramJsonImportResult
    {
        $result = new ProgramJsonImportResult;

        foreach ($programData as $dayIndex => $dayData) {
            $result->days++;

            foreach ($dayData['Salonlar'] ?? [] as $salonData) {
                $result->venues++;

                foreach ($salonData['Oturumlar'] ?? [] as $sessionData) {
                    $result->sessions++;
                    $result->presentations += count($sessionData['OturumIcerikBilgileri'] ?? []);
                    $result->moderatorLinks += $this->countGorevliler($sessionData['GorevliListesi'] ?? []);

                    foreach ($sessionData['OturumIcerikBilgileri'] ?? [] as $contentData) {
                        $result->speakerLinks += $this->countGorevliler($contentData['GorevliListesi'] ?? []);
                    }
                }
            }

            unset($dayIndex);
        }

        $participantKeys = [];
        foreach ($programData as $dayData) {
            foreach ($dayData['Salonlar'] ?? [] as $salonData) {
                foreach ($salonData['Oturumlar'] ?? [] as $sessionData) {
                    foreach ($sessionData['GorevliListesi'] ?? [] as $gorevliGroup) {
                        foreach ($gorevliGroup['Gorevliler'] ?? [] as $gorevli) {
                            $participantKeys[$this->participantKey($gorevli)] = true;
                        }
                    }

                    foreach ($sessionData['OturumIcerikBilgileri'] ?? [] as $contentData) {
                        foreach ($contentData['GorevliListesi'] ?? [] as $gorevliGroup) {
                            foreach ($gorevliGroup['Gorevliler'] ?? [] as $gorevli) {
                                $participantKeys[$this->participantKey($gorevli)] = true;
                            }
                        }
                    }
                }
            }
        }

        $result->participants = count($participantKeys);

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $programData
     */
    private function collectParticipants(Event $event, array $programData): void
    {
        $uniqueGorevliler = [];

        foreach ($programData as $dayData) {
            foreach ($dayData['Salonlar'] ?? [] as $salonData) {
                foreach ($salonData['Oturumlar'] ?? [] as $sessionData) {
                    $this->collectGorevlilerFromList($sessionData['GorevliListesi'] ?? [], $uniqueGorevliler);

                    foreach ($sessionData['OturumIcerikBilgileri'] ?? [] as $contentData) {
                        $this->collectGorevlilerFromList($contentData['GorevliListesi'] ?? [], $uniqueGorevliler);
                    }
                }
            }
        }

        foreach ($uniqueGorevliler as $key => $gorevli) {
            $participant = $this->findOrCreateParticipant($event, $gorevli);
            $this->participantCache[$key] = $participant;
            $this->result->participants++;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $gorevliListesi
     * @param  array<string, array<string, mixed>>  $uniqueGorevliler
     */
    private function collectGorevlilerFromList(array $gorevliListesi, array &$uniqueGorevliler): void
    {
        foreach ($gorevliListesi as $gorevliGroup) {
            foreach ($gorevliGroup['Gorevliler'] ?? [] as $gorevli) {
                $key = $this->participantKey($gorevli);

                if ($key === '') {
                    continue;
                }

                $uniqueGorevliler[$key] = $gorevli;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $programData
     */
    private function importProgramHierarchy(Event $event, array $programData): void
    {
        foreach ($programData as $dayIndex => $dayData) {
            $eventDay = EventDay::create([
                'event_id' => $event->id,
                'date' => $dayData['IsoTarih'],
                'display_name' => $dayData['Tarih'] ?? ('Gün '.($dayIndex + 1)),
                'sort_order' => $dayIndex + 1,
                'is_active' => true,
            ]);
            $this->result->days++;

            foreach ($dayData['Salonlar'] ?? [] as $venueIndex => $salonData) {
                $venue = Venue::create([
                    'event_day_id' => $eventDay->id,
                    'name' => $salonData['Salon'],
                    'display_name' => $salonData['Salon'],
                    'sort_order' => $venueIndex + 1,
                ]);
                $this->result->venues++;

                foreach ($salonData['Oturumlar'] ?? [] as $sessionIndex => $sessionData) {
                    $this->importSession($venue, $sessionData, $sessionIndex + 1);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $sessionData
     */
    private function importSession(Venue $venue, array $sessionData, int $sortOrder): void
    {
        $sessionType = ProgramSessionTypeMapper::fromProgramJsonType($sessionData['OturumTipi'] ?? 'Oturum');
        $programJsonType = $sessionData['OturumTipi'] ?? '';

        if ($programJsonType !== '' && ! ProgramSessionTypeMapper::hasProgramJsonTypeMapping($programJsonType)) {
            $this->result->warnings[] = "Bilinmeyen oturum tipi: {$programJsonType}";
            Log::warning('ProgramJsonImporter: bilinmeyen oturum tipi', ['type' => $programJsonType]);
        }

        $moderatorTitle = $this->resolveModeratorTitle($sessionData['GorevliListesi'] ?? []);

        $session = ProgramSession::create([
            'venue_id' => $venue->id,
            'title' => $sessionData['Oturum'] ?? 'Oturum',
            'description' => $sessionData['Konu'] ?? null,
            'start_time' => $sessionData['BaslangicSaati'] ?? '00:00',
            'end_time' => $sessionData['BitisSaati'] ?? '00:00',
            'session_type' => $sessionType,
            'moderator_title' => $moderatorTitle,
            'is_break' => ! ($sessionData['SaatGosterim'] ?? true),
            'sort_order' => $sortOrder,
        ]);
        $this->result->sessions++;

        $this->attachModerators($session, $sessionData['GorevliListesi'] ?? []);

        foreach ($sessionData['OturumIcerikBilgileri'] ?? [] as $contentIndex => $contentData) {
            $this->importPresentation($session, $contentData, $contentIndex + 1);
        }
    }

    /**
     * @param  array<string, mixed>  $contentData
     */
    private function importPresentation(ProgramSession $session, array $contentData, int $sortOrder): void
    {
        $presentation = Presentation::create([
            'program_session_id' => $session->id,
            'title' => $contentData['OturumIcerik'] ?? 'Sunum',
            'abstract' => $contentData['ExtraBilgi'] ?? null,
            'start_time' => $contentData['BaslangicSaati'] ?? null,
            'end_time' => $contentData['BitisSaati'] ?? null,
            'presentation_type' => 'oral',
            'sort_order' => $sortOrder,
        ]);
        $this->result->presentations++;

        $this->attachSpeakers($presentation, $contentData['GorevliListesi'] ?? []);
    }

    /**
     * @param  array<int, array<string, mixed>>  $gorevliListesi
     */
    private function attachModerators(ProgramSession $session, array $gorevliListesi): void
    {
        $sortOrder = 1;

        foreach ($gorevliListesi as $gorevliGroup) {
            foreach ($gorevliGroup['Gorevliler'] ?? [] as $gorevli) {
                $participant = $this->getCachedParticipant($gorevli);

                if (! $participant) {
                    continue;
                }

                if ($session->moderators()->where('participant_id', $participant->id)->exists()) {
                    continue;
                }

                $session->moderators()->attach($participant->id, ['sort_order' => $sortOrder]);
                $participant->update(['is_moderator' => true]);
                $this->result->moderatorLinks++;
                $sortOrder++;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $gorevliListesi
     */
    private function attachSpeakers(Presentation $presentation, array $gorevliListesi): void
    {
        $sortOrder = 1;

        foreach ($gorevliListesi as $gorevliGroup) {
            foreach ($gorevliGroup['Gorevliler'] ?? [] as $gorevli) {
                $participant = $this->getCachedParticipant($gorevli);

                if (! $participant) {
                    continue;
                }

                if ($presentation->speakers()->where('participant_id', $participant->id)->exists()) {
                    continue;
                }

                $presentation->speakers()->attach($participant->id, [
                    'speaker_role' => 'primary',
                    'sort_order' => $sortOrder,
                ]);
                $participant->update(['is_speaker' => true]);
                $this->result->speakerLinks++;
                $sortOrder++;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $gorevliListesi
     */
    private function resolveModeratorTitle(array $gorevliListesi): string
    {
        foreach ($gorevliListesi as $gorevliGroup) {
            if (! empty($gorevliGroup['GorevliTipi'])) {
                return $gorevliGroup['GorevliTipi'];
            }
        }

        return 'Oturum Başkanı';
    }

    /**
     * @param  array<string, mixed>  $gorevli
     */
    private function findOrCreateParticipant(Event $event, array $gorevli): Participant
    {
        [$firstName, $lastName] = $this->parseName($gorevli['AdSoyad'] ?? '');
        $title = trim($gorevli['Unvan'] ?? '') ?: null;
        $affiliation = trim($gorevli['Kurum'] ?? '') ?: null;

        $existing = Participant::query()
            ->where('organization_id', $event->organization_id)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->first();

        if ($existing) {
            $existing->update(array_filter([
                'title' => $title,
                'affiliation' => $affiliation,
            ], fn ($value) => $value !== null));

            return $existing;
        }

        return Participant::create([
            'organization_id' => $event->organization_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'title' => $title,
            'affiliation' => $affiliation,
            'email' => $this->syntheticEmail($firstName, $lastName),
            'is_speaker' => false,
            'is_moderator' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $gorevli
     */
    private function getCachedParticipant(array $gorevli): ?Participant
    {
        $key = $this->participantKey($gorevli);

        return $key !== '' ? ($this->participantCache[$key] ?? null) : null;
    }

    /**
     * @param  array<string, mixed>  $gorevli
     */
    private function participantKey(array $gorevli): string
    {
        [$firstName, $lastName] = $this->parseName($gorevli['AdSoyad'] ?? '');

        if ($firstName === '' && $lastName === '') {
            return '';
        }

        return Str::lower(trim("{$firstName}|{$lastName}"));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseName(string $fullName): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName) ?? '');

        if ($fullName === '') {
            return ['', ''];
        }

        $parts = explode(' ', $fullName);

        if (count($parts) === 1) {
            return [$parts[0], ''];
        }

        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        return [$firstName, $lastName];
    }

    private function syntheticEmail(string $firstName, string $lastName): string
    {
        $base = Str::slug("{$firstName}-{$lastName}", '.');

        if ($base === '') {
            $base = 'katilimci';
        }

        return "{$base}@import.local";
    }

    /**
     * @param  array<int, array<string, mixed>>  $gorevliListesi
     */
    private function countGorevliler(array $gorevliListesi): int
    {
        $count = 0;

        foreach ($gorevliListesi as $gorevliGroup) {
            $count += count($gorevliGroup['Gorevliler'] ?? []);
        }

        return $count;
    }

    private function clearEventProgram(Event $event): void
    {
        $event->load(['eventDays.venues.programSessions.presentations']);

        foreach ($event->eventDays as $eventDay) {
            foreach ($eventDay->venues as $venue) {
                foreach ($venue->programSessions as $session) {
                    $session->presentations()->each(function (Presentation $presentation) {
                        $presentation->speakers()->detach();
                        $presentation->forceDelete();
                    });
                    $session->moderators()->detach();
                    $session->forceDelete();
                }
                $venue->forceDelete();
            }
            $eventDay->forceDelete();
        }
    }
}
