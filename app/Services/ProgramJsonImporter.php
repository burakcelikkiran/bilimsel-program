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

            foreach ($dayData['Venues'] ?? [] as $venueData) {
                $result->venues++;

                foreach ($venueData['Sessions'] ?? [] as $sessionData) {
                    $result->sessions++;
                    $result->presentations += count($sessionData['SessionContents'] ?? []);
                    $result->moderatorLinks += $this->countStaff($sessionData['StaffList'] ?? []);

                    foreach ($sessionData['SessionContents'] ?? [] as $contentData) {
                        $result->speakerLinks += $this->countStaff($contentData['StaffList'] ?? []);
                    }
                }
            }

            unset($dayIndex);
        }

        $participantKeys = [];
        foreach ($programData as $dayData) {
            foreach ($dayData['Venues'] ?? [] as $venueData) {
                foreach ($venueData['Sessions'] ?? [] as $sessionData) {
                    foreach ($sessionData['StaffList'] ?? [] as $staffGroup) {
                        foreach ($staffGroup['Staff'] ?? [] as $staff) {
                            $participantKeys[$this->participantKey($staff)] = true;
                        }
                    }

                    foreach ($sessionData['SessionContents'] ?? [] as $contentData) {
                        foreach ($contentData['StaffList'] ?? [] as $staffGroup) {
                            foreach ($staffGroup['Staff'] ?? [] as $staff) {
                                $participantKeys[$this->participantKey($staff)] = true;
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
        $uniqueStaff = [];

        foreach ($programData as $dayData) {
            foreach ($dayData['Venues'] ?? [] as $venueData) {
                foreach ($venueData['Sessions'] ?? [] as $sessionData) {
                    $this->collectStaffFromList($sessionData['StaffList'] ?? [], $uniqueStaff);

                    foreach ($sessionData['SessionContents'] ?? [] as $contentData) {
                        $this->collectStaffFromList($contentData['StaffList'] ?? [], $uniqueStaff);
                    }
                }
            }
        }

        foreach ($uniqueStaff as $key => $staff) {
            $participant = $this->findOrCreateParticipant($event, $staff);
            $this->participantCache[$key] = $participant;
            $this->result->participants++;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $staffList
     * @param  array<string, array<string, mixed>>  $uniqueStaff
     */
    private function collectStaffFromList(array $staffList, array &$uniqueStaff): void
    {
        foreach ($staffList as $staffGroup) {
            foreach ($staffGroup['Staff'] ?? [] as $staff) {
                $key = $this->participantKey($staff);

                if ($key === '') {
                    continue;
                }

                $uniqueStaff[$key] = $staff;
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
                'date' => $dayData['IsoDate'],
                'display_name' => $dayData['Date'] ?? ('Gün '.($dayIndex + 1)),
                'sort_order' => $dayIndex + 1,
                'is_active' => true,
            ]);
            $this->result->days++;

            foreach ($dayData['Venues'] ?? [] as $venueIndex => $venueData) {
                $venue = Venue::create([
                    'event_day_id' => $eventDay->id,
                    'name' => $venueData['Venue'],
                    'display_name' => $venueData['Venue'],
                    'sort_order' => $venueIndex + 1,
                ]);
                $this->result->venues++;

                foreach ($venueData['Sessions'] ?? [] as $sessionIndex => $sessionData) {
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
        $sessionType = ProgramSessionTypeMapper::fromProgramJsonType($sessionData['SessionType'] ?? 'Oturum');
        $programJsonType = $sessionData['SessionType'] ?? '';

        if ($programJsonType !== '' && ! ProgramSessionTypeMapper::hasProgramJsonTypeMapping($programJsonType)) {
            $this->result->warnings[] = "Bilinmeyen oturum tipi: {$programJsonType}";
            Log::warning('ProgramJsonImporter: bilinmeyen oturum tipi', ['type' => $programJsonType]);
        }

        $moderatorTitle = $this->resolveModeratorTitle($sessionData['StaffList'] ?? []);

        $session = ProgramSession::create([
            'venue_id' => $venue->id,
            'title' => $sessionData['Session'] ?? 'Oturum',
            'description' => $sessionData['Topic'] ?? null,
            'start_time' => $sessionData['StartTime'] ?? '00:00',
            'end_time' => $sessionData['EndTime'] ?? '00:00',
            'session_type' => $sessionType,
            'moderator_title' => $moderatorTitle,
            'is_break' => ! ($sessionData['ShowTime'] ?? true),
            'sort_order' => $sortOrder,
        ]);
        $this->result->sessions++;

        $this->attachModerators($session, $sessionData['StaffList'] ?? []);

        foreach ($sessionData['SessionContents'] ?? [] as $contentIndex => $contentData) {
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
            'title' => $contentData['SessionContent'] ?? 'Sunum',
            'abstract' => $contentData['ExtraInfo'] ?? null,
            'start_time' => $contentData['StartTime'] ?? null,
            'end_time' => $contentData['EndTime'] ?? null,
            'presentation_type' => 'oral',
            'sort_order' => $sortOrder,
        ]);
        $this->result->presentations++;

        $this->attachSpeakers($presentation, $contentData['StaffList'] ?? []);
    }

    /**
     * @param  array<int, array<string, mixed>>  $staffList
     */
    private function attachModerators(ProgramSession $session, array $staffList): void
    {
        $sortOrder = 1;

        foreach ($staffList as $staffGroup) {
            foreach ($staffGroup['Staff'] ?? [] as $staff) {
                $participant = $this->getCachedParticipant($staff);

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
     * @param  array<int, array<string, mixed>>  $staffList
     */
    private function attachSpeakers(Presentation $presentation, array $staffList): void
    {
        $sortOrder = 1;

        foreach ($staffList as $staffGroup) {
            foreach ($staffGroup['Staff'] ?? [] as $staff) {
                $participant = $this->getCachedParticipant($staff);

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
     * @param  array<int, array<string, mixed>>  $staffList
     */
    private function resolveModeratorTitle(array $staffList): string
    {
        foreach ($staffList as $staffGroup) {
            if (! empty($staffGroup['StaffType'])) {
                return $staffGroup['StaffType'];
            }
        }

        return 'Oturum Başkanı';
    }

    /**
     * @param  array<string, mixed>  $staff
     */
    private function findOrCreateParticipant(Event $event, array $staff): Participant
    {
        [$firstName, $lastName] = $this->parseName($staff['FullName'] ?? '');
        $title = trim($staff['Title'] ?? '') ?: null;
        $affiliation = trim($staff['Institution'] ?? '') ?: null;

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
     * @param  array<string, mixed>  $staff
     */
    private function getCachedParticipant(array $staff): ?Participant
    {
        $key = $this->participantKey($staff);

        return $key !== '' ? ($this->participantCache[$key] ?? null) : null;
    }

    /**
     * @param  array<string, mixed>  $staff
     */
    private function participantKey(array $staff): string
    {
        [$firstName, $lastName] = $this->parseName($staff['FullName'] ?? '');

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
     * @param  array<int, array<string, mixed>>  $staffList
     */
    private function countStaff(array $staffList): int
    {
        $count = 0;

        foreach ($staffList as $staffGroup) {
            $count += count($staffGroup['Staff'] ?? []);
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
