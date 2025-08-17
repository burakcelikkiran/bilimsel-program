<?php

namespace App\Policies;

use App\Models\ProgramSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any program sessions.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar oturum listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the program session.
     */
    public function view(User $user, ProgramSession $programSession): bool
    {
        // Admin tüm oturumları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu oturumun etkinliğinin organizasyonuna bağlı mı?
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create program sessions.
     */
    public function create(User $user): bool
    {
        // Admin her zaman oturum oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor oturum oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the program session.
     */
    public function update(User $user, ProgramSession $programSession): bool
    {
        // Admin tüm oturumları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Oturumun etkinliğinin organizasyonunda organizer veya editor rolü var mı?
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the program session.
     */
    public function delete(User $user, ProgramSession $programSession): bool
    {
        // Sunumları olan oturum silinemez
        if ($programSession->presentations()->exists()) {
            return false;
        }

        // Admin tüm oturumları silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the program session.
     */
    public function restore(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can permanently delete the program session.
     */
    public function forceDelete(User $user, ProgramSession $programSession): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create presentations in this session.
     */
    public function createPresentations(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can manage moderators for this session.
     */
    public function manageModerators(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can assign sponsors to this session.
     */
    public function manageSponsors(User $user, ProgramSession $programSession): bool
    {
        // Admin tüm sponsor atamalarını yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer sponsor ataması yapabilir
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can duplicate this session.
     */
    public function duplicate(User $user, ProgramSession $programSession): bool
    {
        // Oturumu görebilir ve yeni oturum oluşturabilir mi?
        return $this->view($user, $programSession) && $this->create($user);
    }

    /**
     * Determine whether the user can reorder sessions.
     */
    public function reorder(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can move session to different time slot.
     */
    public function moveTimeSlot(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can view session statistics.
     */
    public function viewStatistics(User $user, ProgramSession $programSession): bool
    {
        return $this->view($user, $programSession);
    }

    /**
     * Determine whether the user can export session data.
     */
    public function export(User $user, ProgramSession $programSession): bool
    {
        return $this->view($user, $programSession);
    }

    /**
     * Determine whether the user can manage session categories.
     */
    public function manageCategories(User $user, ProgramSession $programSession): bool
    {
        return $this->update($user, $programSession);
    }

    /**
     * Determine whether the user can view session attendance.
     */
    public function viewAttendance(User $user, ProgramSession $programSession): bool
    {
        return $this->view($user, $programSession);
    }

    /**
     * Determine whether the user can manage session recordings.
     */
    public function manageRecordings(User $user, ProgramSession $programSession): bool
    {
        // Admin tüm kayıtları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer kayıt yönetebilir
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can send session notifications.
     */
    public function sendNotifications(User $user, ProgramSession $programSession): bool
    {
        // Admin tüm bildirimleri gönderebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bildirim gönderebilir
        $event = $programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can check in attendees to this session.
     */
    public function checkInAttendees(User $user, ProgramSession $programSession): bool
    {
        return $this->view($user, $programSession);
    }
}