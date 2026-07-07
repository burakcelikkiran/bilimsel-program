<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;

class AdminNotificationService
{
    /**
     * @return array{notifications: array<int, array<string, mixed>>, unread_count: int}
     */
    public function forUser(User $user): array
    {
        $notifications = collect();

        $soonEvents = Event::upcoming()
            ->where('start_date', '<=', now()->addDays(7))
            ->where('start_date', '>', now())
            ->published();

        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $soonEvents->whereIn('organization_id', $organizationIds);
        }

        foreach ($soonEvents->get() as $event) {
            $daysUntil = now()->diffInDays($event->start_date);
            $notifications->push([
                'type' => 'event_starting_soon',
                'title' => 'Etkinlik Yaklaşıyor',
                'message' => "'{$event->name}' etkinliği {$daysUntil} gün içinde başlayacak",
                'date' => $event->start_date,
                'link' => route('admin.events.show', $event),
                'priority' => 'medium',
            ]);
        }

        $eventsWithoutSessions = Event::whereDoesntHave('eventDays.venues.programSessions')
            ->where('start_date', '>', now())
            ->published();

        if (! $user->isAdmin()) {
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $eventsWithoutSessions->whereIn('organization_id', $organizationIds);
        }

        foreach ($eventsWithoutSessions->get() as $event) {
            $notifications->push([
                'type' => 'event_incomplete',
                'title' => 'Eksik Program',
                'message' => "'{$event->name}' etkinliğinde henüz oturum bulunmuyor",
                'date' => $event->created_at,
                'link' => route('admin.events.show', $event),
                'priority' => 'high',
            ]);
        }

        $items = $notifications->sortByDesc('date')->take(10)->values()->all();

        return [
            'notifications' => $items,
            'unread_count' => count($items),
        ];
    }
}
