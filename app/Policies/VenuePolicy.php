<?php

namespace App\Policies;

use App\Models\Venue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class VenuePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any venues.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar venue listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the venue.
     */
    public function view(User $user, Venue $venue): bool
    {
        // Admin tüm venue'ları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu venue'nun organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $venue->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create venues.
     */
    public function create(User $user): bool
    {
        // Admin her zaman venue oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor venue oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the venue.
     */
    public function update(User $user, Venue $venue): bool
    {
        // Admin tüm venue'ları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Venue'nun organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $venue->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the venue.
     */
    public function delete(User $user, Venue $venue): bool
    {
        // Admin tüm venue'ları silebilir (cascade delete ile)
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        return $user->organizations()
                   ->where('organizations.id', $venue->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the venue.
     */
    public function restore(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }

    /**
     * Determine whether the user can permanently delete the venue.
     */
    public function forceDelete(User $user, Venue $venue): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage venue sessions.
     */
    public function manageSessions(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }

    /**
     * Determine whether the user can view venue schedule.
     */
    public function viewSchedule(User $user, Venue $venue): bool
    {
        return $this->view($user, $venue);
    }

    /**
     * Determine whether the user can manage venue equipment.
     */
    public function manageEquipment(User $user, Venue $venue): bool
    {
        // Admin tüm ekipmanları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ekipman yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $venue->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view venue statistics.
     */
    public function viewStatistics(User $user, Venue $venue): bool
    {
        return $this->view($user, $venue);
    }

    /**
     * Determine whether the user can export venue data.
     */
    public function export(User $user, Venue $venue): bool
    {
        return $this->view($user, $venue);
    }

    /**
     * Determine whether the user can duplicate the venue.
     */
    public function duplicate(User $user, Venue $venue): bool
    {
        // Venue'yu görebilir ve yeni venue oluşturabilir mi?
        return $this->view($user, $venue) && $this->create($user);
    }

    /**
     * Determine whether the user can manage venue bookings.
     */
    public function manageBookings(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }

    /**
     * Determine whether the user can view venue availability.
     */
    public function viewAvailability(User $user, Venue $venue): bool
    {
        return $this->view($user, $venue);
    }

    /**
     * Determine whether the user can update venue sort order.
     */
    public function updateSortOrder(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }

    /**
     * Determine whether the user can toggle venue status.
     */
    public function toggleStatus(User $user, Venue $venue): bool
    {
        // Admin tüm venue durumlarını değiştirebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer venue durumunu değiştirebilir
        return $user->organizations()
                   ->where('organizations.id', $venue->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}