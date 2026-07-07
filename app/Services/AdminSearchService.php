<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ProgramSession;
use App\Models\Sponsor;
use App\Models\User;
use App\Models\Venue;

class AdminSearchService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(User $user, string $query): array
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return [];
        }

        $results = [];

        $events = Event::query()->search($query)->limit(5);
        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $events->whereIn('organization_id', $organizationIds);
        }

        foreach ($events->get(['id', 'name', 'slug']) as $event) {
            $results[] = [
                'type' => 'events',
                'id' => $event->id,
                'title' => $event->name,
                'subtitle' => 'Etkinlik',
                'url' => route('admin.events.show', $event),
            ];
        }

        $participants = Participant::query()->search($query)->limit(5);
        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $participants->whereIn('organization_id', $organizationIds);
        }

        foreach ($participants->get(['id', 'first_name', 'last_name', 'title']) as $participant) {
            $results[] = [
                'type' => 'participants',
                'id' => $participant->id,
                'title' => trim($participant->first_name.' '.$participant->last_name),
                'subtitle' => $participant->title ?: 'Katılımcı',
                'url' => route('admin.participants.show', $participant),
            ];
        }

        $sessions = ProgramSession::query()
            ->where('title', 'like', "%{$query}%")
            ->with('venue.eventDay.event')
            ->limit(5);

        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sessions->whereHas('venue.eventDay.event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            });
        }

        foreach ($sessions->get() as $session) {
            $results[] = [
                'type' => 'sessions',
                'id' => $session->id,
                'title' => $session->title,
                'subtitle' => 'Program Oturumu',
                'url' => route('admin.program-sessions.show', $session),
            ];
        }

        $venues = Venue::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('display_name', 'like', "%{$query}%");
            })
            ->limit(5);

        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $venues->whereHas('eventDay.event', function ($q) use ($organizationIds) {
                $q->whereIn('organization_id', $organizationIds);
            });
        }

        foreach ($venues->get(['id', 'name', 'display_name']) as $venue) {
            $results[] = [
                'type' => 'venues',
                'id' => $venue->id,
                'title' => $venue->display_name ?? $venue->name,
                'subtitle' => 'Salon',
                'url' => route('admin.venues.show', $venue),
            ];
        }

        $sponsors = Sponsor::query()->where('name', 'like', "%{$query}%")->limit(5);
        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $sponsors->whereIn('organization_id', $organizationIds);
        }

        foreach ($sponsors->get(['id', 'name']) as $sponsor) {
            $results[] = [
                'type' => 'sponsors',
                'id' => $sponsor->id,
                'title' => $sponsor->name,
                'subtitle' => 'Sponsor',
                'url' => route('admin.sponsors.show', $sponsor),
            ];
        }

        return $results;
    }
}
