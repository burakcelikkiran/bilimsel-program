<?php

namespace App\Policies;

use App\Models\EventDay;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventDayPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any event days.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar etkinlik günleri listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the event day.
     */
    public function view(User $user, EventDay $eventDay): bool
    {
        // Admin tüm etkinlik günlerini görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu günün etkinliğinin organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create event days.
     */
    public function create(User $user): bool
    {
        // Admin her zaman etkinlik günü oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor etkinlik günü oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the event day.
     */
    public function update(User $user, EventDay $eventDay): bool
    {
        // Admin tüm etkinlik günlerini güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Günün etkinliğinin organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the event day.
     */
    public function delete(User $user, EventDay $eventDay): bool
    {
        // Oturumları olan gün silinemez
        if ($eventDay->venues()->whereHas('programSessions')->exists()) {
            return false;
        }

        // Admin tüm etkinlik günlerini silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the event day.
     */
    public function restore(User $user, EventDay $eventDay): bool
    {
        return $this->update($user, $eventDay);
    }

    /**
     * Determine whether the user can permanently delete the event day.
     */
    public function forceDelete(User $user, EventDay $eventDay): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can duplicate the event day.
     */
    public function duplicate(User $user, EventDay $eventDay): bool
    {
        // Günü görebilir ve yeni gün oluşturabilir mi?
        return $this->view($user, $eventDay) && $this->create($user);
    }

    /**
     * Determine whether the user can toggle day status.
     */
    public function toggleStatus(User $user, EventDay $eventDay): bool
    {
        // Admin tüm gün durumlarını değiştirebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer gün durumunu değiştirebilir
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can update event day sort order.
     */
    public function updateSortOrder(User $user, EventDay $eventDay): bool
    {
        return $this->update($user, $eventDay);
    }

    /**
     * Determine whether the user can manage venues for this day.
     */
    public function manageVenues(User $user, EventDay $eventDay): bool
    {
        return $this->update($user, $eventDay);
    }

    /**
     * Determine whether the user can view day schedule.
     */
    public function viewSchedule(User $user, EventDay $eventDay): bool
    {
        return $this->view($user, $eventDay);
    }

    /**
     * Determine whether the user can generate automatic days.
     */
    public function generateDays(User $user): bool
    {
        // Admin otomatik gün oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer otomatik gün oluşturabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view day statistics.
     */
    public function viewStatistics(User $user, EventDay $eventDay): bool
    {
        return $this->view($user, $eventDay);
    }

    /**
     * Determine whether the user can export day data.
     */
    public function export(User $user, EventDay $eventDay): bool
    {
        return $this->view($user, $eventDay);
    }

    /**
     * Determine whether the user can manage day notifications.
     */
    public function manageNotifications(User $user, EventDay $eventDay): bool
    {
        // Admin tüm bildirimleri yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bildirim yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view day attendees.
     */
    public function viewAttendees(User $user, EventDay $eventDay): bool
    {
        return $this->view($user, $eventDay);
    }

    /**
     * Determine whether the user can manage day settings.
     */
    public function manageSettings(User $user, EventDay $eventDay): bool
    {
        // Admin tüm ayarları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ayar yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can copy sessions from another day.
     */
    public function copySessions(User $user, EventDay $eventDay): bool
    {
        return $this->update($user, $eventDay);
    }

    /**
     * Determine whether the user can bulk import sessions to this day.
     */
    public function bulkImport(User $user, EventDay $eventDay): bool
    {
        // Admin bulk import yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bulk import yapabilir
        return $user->organizations()
                   ->where('organizations.id', $eventDay->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}