<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar etkinlik listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        // Admin tüm etkinlikleri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu etkinliğin organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        // Admin her zaman etkinlik oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer rolündeki kullanıcılar etkinlik oluşturabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        // Admin tüm etkinlikleri güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Event'in organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        // Yayınlanmış etkinlik silinemez
        if ($event->is_published) {
            return false;
        }

        // Admin tüm etkinlikleri silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir veya etkinliği oluşturan
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists() || $event->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can publish/unpublish the event.
     */
    public function publish(User $user, Event $event): bool
    {
        // Admin tüm etkinlikleri yayınlayabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer yayınlayabilir
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can create event days.
     */
    public function createDays(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /**
     * Determine whether the user can manage the event program.
     */
    public function manageProgram(User $user, Event $event): bool
    {
        // Admin tüm programları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor program yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can manage event participants.
     */
    public function manageParticipants(User $user, Event $event): bool
    {
        return $this->manageProgram($user, $event);
    }

    /**
     * Determine whether the user can manage event sponsors.
     */
    public function manageSponsors(User $user, Event $event): bool
    {
        // Admin tüm sponsorları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer sponsor yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can duplicate the event.
     */
    public function duplicate(User $user, Event $event): bool
    {
        // Etkinliği görebilir ve yeni etkinlik oluşturabilir mi?
        return $this->view($user, $event) && $this->create($user);
    }

    /**
     * Determine whether the user can export event data.
     */
    public function export(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    /**
     * Determine whether the user can view event statistics.
     */
    public function viewStatistics(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    /**
     * Determine whether the user can manage event venues.
     */
    public function manageVenues(User $user, Event $event): bool
    {
        return $this->manageProgram($user, $event);
    }

    /**
     * Determine whether the user can view event financial data.
     */
    public function viewFinancials(User $user, Event $event): bool
    {
        // Admin tüm finansal verileri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer finansal verileri görebilir
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can send event notifications.
     */
    public function sendNotifications(User $user, Event $event): bool
    {
        // Admin tüm bildirimleri gönderebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bildirim gönderebilir
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can import data to the event.
     */
    public function import(User $user, Event $event): bool
    {
        return $this->manageProgram($user, $event);
    }
}