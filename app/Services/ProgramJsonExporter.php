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
            'Tarih' => $this->formatTurkishDate($day->date),
            'IsoTarih' => $day->date->toDateString(),
            'Salonlar' => $day->venues
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
            'Salon' => $venue->display_name ?? $venue->name,
            'Oturumlar' => $venue->programSessions
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
            'OturumTipiID' => ProgramSessionTypeMapper::typeUuid($session->session_type),
            'OturumTipi' => ProgramSessionTypeMapper::programJsonType($session->session_type),
            'BaslangicTarihi' => $this->formatTurkishDate($day->date),
            'BaslangicSaati' => $startTime,
            'BaslangicTarihiJSON' => $this->formatDateTimeJson($day->date, $startTime),
            'BitisTarihi' => $this->formatTurkishDate($day->date),
            'BitisSaati' => $endTime,
            'BitisTarihiJSON' => $this->formatDateTimeJson($day->date, $endTime),
            'OturumID' => ProgramSessionTypeMapper::sessionUuid($session->id),
            'LogoDurum' => false,
            'SaatGosterim' => ! ($session->is_break ?? false),
            'Oturum' => $session->title,
            'Konu' => $session->description ?? '',
            'ExtraBilgi' => '',
            'GorevliListesi' => $this->mapSessionGorevliListesi($session),
            'OturumIcerikBilgileri' => $session->presentations
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
            'BaslangicTarihi' => $this->formatTurkishDate($day->date),
            'BaslangicSaati' => $startTime,
            'BaslangicTarihiJSON' => $this->formatDateTimeJson($day->date, $startTime),
            'BitisTarihi' => $this->formatTurkishDate($day->date),
            'BitisSaati' => $endTime,
            'BitisTarihiJSON' => $this->formatDateTimeJson($day->date, $endTime),
            'OturumIcerik' => $presentation->title,
            'ExtraBilgi' => $presentation->abstract ?? '',
            'GorevliListesi' => $this->mapPresentationGorevliListesi($presentation->speakers),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapSessionGorevliListesi(ProgramSession $session): array
    {
        if ($session->moderators->isEmpty()) {
            return [];
        }

        return [
            [
                'GorevliTipi' => $session->moderator_title ?: 'Oturum Başkanı',
                'Gorevliler' => $session->moderators
                    ->map(fn (Participant $moderator) => $this->mapGorevli($moderator))
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @param  Collection<int, Participant>  $speakers
     * @return array<int, array<string, mixed>>
     */
    private function mapPresentationGorevliListesi(Collection $speakers): array
    {
        if ($speakers->isEmpty()) {
            return [];
        }

        $gorevliTipi = $speakers->count() === 1 ? 'Konuşmacı' : 'Konuşmacılar';

        return [
            [
                'GorevliTipi' => $gorevliTipi,
                'Gorevliler' => $speakers
                    ->map(fn (Participant $speaker) => $this->mapGorevli($speaker))
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mapGorevli(Participant $participant): array
    {
        return [
            'Unvan' => $participant->title ?? '',
            'AdSoyad' => trim($participant->first_name.' '.$participant->last_name),
            'Kurum' => $participant->affiliation ?? '',
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
