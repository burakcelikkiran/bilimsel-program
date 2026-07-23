<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventDay;
use App\Models\Participant;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Support\ProgramSessionTypeMapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ProgramJsonExporter
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function export(Event $event): array
    {
        $this->loadEventRelations($event);

        return $event->eventDays
            ->map(fn (EventDay $day) => $this->mapDay($day))
            ->values()
            ->all();
    }

    private function loadEventRelations(Event $event): void
    {
        $event->load([
            'eventDays' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('date')
                    ->orderBy('sort_order');
            },
            'eventDays.venues' => function ($query) {
                $query->orderBy('sort_order');
            },
            'eventDays.venues.programSessions' => function ($query) {
                $query->with([
                    'moderators',
                    'presentations' => function ($presentationQuery) {
                        $presentationQuery->with('speakers')
                            ->orderBy('start_time')
                            ->orderBy('sort_order');
                    },
                ])->orderBy('start_time')->orderBy('sort_order');
            },
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDay(EventDay $day): array
    {
        return [
            'Date' => $this->formatTurkishDate($day->date),
            'IsoDate' => $day->date->toDateString(),
            'Venues' => $day->venues
                ->map(fn ($venue) => $this->mapVenue($venue, $day))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVenue($venue, EventDay $day): array
    {
        return [
            'Venue' => $venue->display_name ?? $venue->name,
            'Sessions' => $venue->programSessions
                ->map(fn (ProgramSession $session) => $this->mapSession($session, $day))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSession(ProgramSession $session, EventDay $day): array
    {
        $startTime = $this->formatTime($session->start_time);
        $endTime = $this->formatTime($session->end_time);

        return [
            'SessionTypeID' => ProgramSessionTypeMapper::typeUuid($session->session_type),
            'SessionType' => ProgramSessionTypeMapper::programJsonType($session->session_type),
            'StartDate' => $this->formatTurkishDate($day->date),
            'StartTime' => $startTime,
            'StartDateJSON' => $this->formatDateTimeJson($day->date, $startTime),
            'EndDate' => $this->formatTurkishDate($day->date),
            'EndTime' => $endTime,
            'EndDateJSON' => $this->formatDateTimeJson($day->date, $endTime),
            'SessionID' => ProgramSessionTypeMapper::sessionUuid($session->id),
            'LogoStatus' => false,
            'ShowTime' => ! ($session->is_break ?? false),
            'Session' => $session->title,
            'Topic' => $session->description ?? '',
            'ExtraInfo' => '',
            'StaffList' => $this->mapSessionStaffList($session),
            'SessionContents' => $session->presentations
                ->map(fn (Presentation $presentation) => $this->mapPresentation($presentation, $day))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPresentation(Presentation $presentation, EventDay $day): array
    {
        $startTime = $this->formatTime($presentation->start_time);
        $endTime = $this->formatTime($presentation->end_time);

        return [
            'StartDate' => $this->formatTurkishDate($day->date),
            'StartTime' => $startTime,
            'StartDateJSON' => $this->formatDateTimeJson($day->date, $startTime),
            'EndDate' => $this->formatTurkishDate($day->date),
            'EndTime' => $endTime,
            'EndDateJSON' => $this->formatDateTimeJson($day->date, $endTime),
            'SessionContent' => $presentation->title,
            'ExtraInfo' => $presentation->abstract ?? '',
            'StaffList' => $this->mapPresentationStaffList($presentation->speakers),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapSessionStaffList(ProgramSession $session): array
    {
        if ($session->moderators->isEmpty()) {
            return [];
        }

        return [
            [
                'StaffType' => $session->moderator_title ?: 'Oturum Başkanı',
                'Staff' => $session->moderators
                    ->map(fn (Participant $moderator) => $this->mapStaff($moderator))
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @param  Collection<int, Participant>  $speakers
     * @return array<int, array<string, mixed>>
     */
    private function mapPresentationStaffList(Collection $speakers): array
    {
        if ($speakers->isEmpty()) {
            return [];
        }

        $staffType = $speakers->count() === 1 ? 'Konuşmacı' : 'Konuşmacılar';

        return [
            [
                'StaffType' => $staffType,
                'Staff' => $speakers
                    ->map(fn (Participant $speaker) => $this->mapStaff($speaker))
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mapStaff(Participant $participant): array
    {
        return [
            'Title' => $participant->title ?? '',
            'FullName' => trim($participant->first_name.' '.$participant->last_name),
            'Institution' => $participant->affiliation ?? '',
        ];
    }

    private function formatTurkishDate(Carbon $date): string
    {
        return $date->format('d.m.Y');
    }

    private function formatTime(mixed $time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        if (is_string($time) && $time !== '') {
            return Carbon::parse($time)->format('H:i');
        }

        return '';
    }

    private function formatDateTimeJson(Carbon $date, string $time): string
    {
        $seconds = strlen($time) === 5 ? ':00' : '';
        $isoDateTime = $date->format('Y-m-d').'T'.$time.$seconds;

        return '"'.$isoDateTime.'"';
    }
}
