<?php

namespace App\Services;

use App\Models\Event;
use App\Support\ProgramSessionTypeMapper;
use Carbon\Carbon;

class EventParticipantDirectoryBuilder
{
    /**
     * @return array{participants: array<int, array<string, mixed>>, total: int}
     */
    public function build(Event $event): array
    {
        $event->load([
            'eventDays' => function ($query) {
                $query->where('is_active', true)->orderBy('date');
            },
            'eventDays.venues' => function ($query) {
                $query->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'presentations' => function ($presentationQuery) {
                        $presentationQuery->with('speakers')->orderBy('sort_order');
                    },
                    'moderators',
                ])->orderBy('start_time');
            },
        ]);

        /** @var array<int, array<string, mixed>> $directory */
        $directory = [];

        foreach ($event->eventDays as $day) {
            foreach ($day->venues as $venue) {
                foreach ($venue->programSessions ?? [] as $session) {
                    $sessionContext = [
                        'session_id' => $session->id,
                        'session_title' => $session->title,
                        'session_type' => $session->session_type,
                        'session_type_label' => ProgramSessionTypeMapper::displayLabel($session->session_type ?? 'main'),
                        'moderator_title' => $session->moderator_title,
                        'day_id' => $day->id,
                        'day_title' => $day->display_name,
                        'day_date' => $day->date->format('Y-m-d'),
                        'formatted_date' => $this->formatDateTurkish($day->date),
                        'venue_id' => $venue->id,
                        'venue_name' => $venue->display_name ?: $venue->name,
                        'venue_color' => $venue->color,
                        'start_time' => $this->formatTime($session->start_time),
                        'end_time' => $this->formatTime($session->end_time),
                    ];

                    foreach ($session->moderators ?? [] as $moderator) {
                        $this->appendRole($directory, $moderator, [
                            'type' => 'moderator',
                            'role_label' => $session->moderator_title ?: 'Moderatör',
                            ...$sessionContext,
                        ]);
                    }

                    foreach ($session->presentations ?? [] as $presentation) {
                        foreach ($presentation->speakers ?? [] as $speaker) {
                            $this->appendRole($directory, $speaker, [
                                'type' => 'speaker',
                                'role_label' => 'Konuşmacı',
                                'speaker_role' => $speaker->pivot->speaker_role ?? 'primary',
                                'speaker_role_label' => $this->speakerRoleLabel($speaker->pivot->speaker_role ?? 'primary'),
                                'presentation_id' => $presentation->id,
                                'presentation_title' => $presentation->title,
                                'presentation_type' => $presentation->presentation_type,
                                'presentation_start_time' => $this->formatTime($presentation->start_time),
                                'presentation_end_time' => $this->formatTime($presentation->end_time),
                                ...$sessionContext,
                            ]);
                        }
                    }
                }
            }
        }

        $participants = collect($directory)
            ->map(function (array $entry) {
                $roles = collect($entry['roles'])
                    ->sortBy([
                        ['day_date', 'asc'],
                        ['start_time', 'asc'],
                        ['type', 'asc'],
                    ])
                    ->values()
                    ->all();

                $roleLabels = collect($roles)
                    ->pluck('role_label')
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'id' => $entry['id'],
                    'full_name' => $entry['full_name'],
                    'title' => $entry['title'],
                    'affiliation' => $entry['affiliation'],
                    'participation_count' => count($roles),
                    'role_labels' => $roleLabels,
                    'roles' => $roles,
                ];
            })
            ->sortBy(fn (array $participant) => mb_strtolower($participant['full_name'], 'UTF-8'))
            ->values()
            ->all();

        return [
            'participants' => $participants,
            'total' => count($participants),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $directory
     * @param  array<string, mixed>  $role
     */
    private function appendRole(array &$directory, $participant, array $role): void
    {
        $participantId = $participant->id;

        if (! array_key_exists($participantId, $directory)) {
            $directory[$participantId] = [
                'id' => $participantId,
                'full_name' => $participant->full_name ?? trim($participant->first_name.' '.$participant->last_name),
                'title' => $participant->title,
                'affiliation' => $participant->affiliation,
                'roles' => [],
            ];
        }

        $directory[$participantId]['roles'][] = $role;
    }

    private function speakerRoleLabel(string $role): string
    {
        return match ($role) {
            'primary' => 'Ana Konuşmacı',
            'co_speaker' => 'Ortak Konuşmacı',
            'discussant' => 'Tartışmacı',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    private function formatTime(mixed $time): ?string
    {
        if ($time === null) {
            return null;
        }

        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        if (is_string($time) && strlen($time) >= 5) {
            return substr($time, 0, 5);
        }

        return (string) $time;
    }

    private function formatDateTurkish(Carbon $date): string
    {
        $months = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        return $date->day.' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }
}
