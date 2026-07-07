<?php

namespace App\Services;

use App\Models\Event;
use App\Support\ProgramSessionTypeMapper;
use Carbon\Carbon;

class EventProgramBuilder
{
    /**
     * @return array{event: array<string, mixed>, statistics: array<string, int>, days: array<int, array<string, mixed>>}
     */
    public function build(Event $event): array
    {
        $event->load([
            'organization',
            'eventDays' => function ($query) {
                $query->where('is_active', true)->orderBy('date');
            },
            'eventDays.venues' => function ($query) {
                $query->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'sponsor',
                    'categories',
                    'presentations' => function ($presentationQuery) {
                        $presentationQuery->with('speakers')->orderBy('sort_order');
                    },
                    'moderators',
                ])->orderBy('start_time');
            },
        ]);

        $totalVenues = 0;
        $totalSessions = 0;
        $totalPresentations = 0;

        foreach ($event->eventDays as $day) {
            $totalVenues += $day->venues->count();
            foreach ($day->venues as $venue) {
                if ($venue->programSessions) {
                    $totalSessions += $venue->programSessions->count();
                    foreach ($venue->programSessions as $session) {
                        if ($session->presentations) {
                            $totalPresentations += $session->presentations->count();
                        }
                    }
                }
            }
        }

        return [
            'event' => $this->mapEvent($event),
            'statistics' => [
                'total_days' => $event->eventDays->count(),
                'total_venues' => $totalVenues,
                'total_sessions' => $totalSessions,
                'total_presentations' => $totalPresentations,
            ],
            'days' => $event->eventDays->map(fn ($day) => $this->mapDay($day))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'timezone' => $event->timezone,
            'venue_address' => $event->venue_address,
            'contact_email' => $event->contact_email,
            'contact_phone' => $event->contact_phone,
            'website_url' => $event->website_url,
            'organization' => $event->organization ? [
                'id' => $event->organization->id,
                'name' => $event->organization->name,
                'website_url' => $event->organization->website_url ?? null,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDay($day): array
    {
        return [
            'id' => $day->id,
            'title' => $day->display_name,
            'date' => $day->date->format('Y-m-d'),
            'formatted_date' => $this->formatDateTurkish($day->date),
            'day_name' => $this->formatDayNameTurkish($day->date),
            'venues' => $day->venues->map(fn ($venue) => $this->mapVenue($venue))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVenue($venue): array
    {
        return [
            'id' => $venue->id,
            'name' => $venue->name,
            'display_name' => $venue->display_name,
            'color' => $venue->color,
            'capacity' => $venue->capacity,
            'sessions' => $venue->programSessions
                ? $venue->programSessions->map(fn ($session) => $this->mapSession($session))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSession($session): array
    {
        return [
            'id' => $session->id,
            'title' => $session->title,
            'description' => $session->description,
            'start_time' => $this->formatTime($session->start_time),
            'end_time' => $this->formatTime($session->end_time),
            'session_type' => $session->session_type,
            'type_label' => ProgramSessionTypeMapper::displayLabel($session->session_type ?? 'main'),
            'is_break' => $session->is_break ?? false,
            'moderator_title' => $session->moderator_title,
            'sponsor' => $session->sponsor ? [
                'id' => $session->sponsor->id,
                'name' => $session->sponsor->name,
                'logo' => $session->sponsor->logo
                    ? asset('storage/'.$session->sponsor->logo)
                    : null,
            ] : null,
            'categories' => $session->categories
                ? $session->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                ])->values()->all()
                : [],
            'moderators' => $session->moderators
                ? $session->moderators->map(fn ($moderator) => $this->mapParticipant($moderator))->values()->all()
                : [],
            'presentations' => $session->presentations
                ? $session->presentations->map(fn ($presentation) => $this->mapPresentation($presentation))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPresentation($presentation): array
    {
        return [
            'id' => $presentation->id,
            'title' => $presentation->title,
            'abstract' => $presentation->abstract,
            'start_time' => $this->formatTime($presentation->start_time),
            'end_time' => $this->formatTime($presentation->end_time),
            'duration_minutes' => $presentation->duration_minutes,
            'presentation_type' => $presentation->presentation_type,
            'sort_order' => $presentation->sort_order,
            'speakers' => $presentation->speakers
                ? $presentation->speakers->map(fn ($speaker) => $this->mapParticipant($speaker))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapParticipant($participant): array
    {
        return [
            'id' => $participant->id,
            'full_name' => $participant->full_name ?? trim($participant->first_name.' '.$participant->last_name),
            'title' => $participant->title,
            'affiliation' => $participant->affiliation,
            'bio' => $participant->bio ?? null,
        ];
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

    private function formatDayNameTurkish(Carbon $date): string
    {
        $days = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Salı',
            'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar',
        ];

        return $days[$date->format('l')] ?? $date->format('l');
    }
}
