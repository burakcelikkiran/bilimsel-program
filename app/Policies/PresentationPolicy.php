<?php

namespace App\Policies;

use App\Models\Presentation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PresentationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any presentations.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar sunum listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the presentation.
     */
    public function view(User $user, Presentation $presentation): bool
    {
        // Admin tüm sunumları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu sunumun etkinliğinin organizasyonuna bağlı mı?
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create presentations.
     */
    public function create(User $user): bool
    {
        // Admin her zaman sunum oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor sunum oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the presentation.
     */
    public function update(User $user, Presentation $presentation): bool
    {
        // Admin tüm sunumları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sunumun etkinliğinin organizasyonunda organizer veya editor rolü var mı?
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the presentation.
     */
    public function delete(User $user, Presentation $presentation): bool
    {
        // Admin tüm sunumları silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the presentation.
     */
    public function restore(User $user, Presentation $presentation): bool
    {
        return $this->update($user, $presentation);
    }

    /**
     * Determine whether the user can permanently delete the presentation.
     */
    public function forceDelete(User $user, Presentation $presentation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage speakers for this presentation.
     */
    public function manageSpeakers(User $user, Presentation $presentation): bool
    {
        return $this->update($user, $presentation);
    }

    /**
     * Determine whether the user can assign sponsors to this presentation.
     */
    public function manageSponsors(User $user, Presentation $presentation): bool
    {
        // Admin tüm sponsor atamalarını yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer sponsor ataması yapabilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can duplicate this presentation.
     */
    public function duplicate(User $user, Presentation $presentation): bool
    {
        // Sunumu görebilir ve yeni sunum oluşturabilir mi?
        return $this->view($user, $presentation) && $this->create($user);
    }

    /**
     * Determine whether the user can reorder presentations.
     */
    public function reorder(User $user, Presentation $presentation): bool
    {
        return $this->update($user, $presentation);
    }

    /**
     * Determine whether the user can move presentation to different session.
     */
    public function moveToSession(User $user, Presentation $presentation): bool
    {
        return $this->update($user, $presentation);
    }

    /**
     * Determine whether the user can upload files for this presentation.
     */
    public function manageFiles(User $user, Presentation $presentation): bool
    {
        return $this->update($user, $presentation);
    }

    /**
     * Determine whether the user can view presentation statistics.
     */
    public function viewStatistics(User $user, Presentation $presentation): bool
    {
        return $this->view($user, $presentation);
    }

    /**
     * Determine whether the user can export presentation data.
     */
    public function export(User $user, Presentation $presentation): bool
    {
        return $this->view($user, $presentation);
    }

    /**
     * Determine whether the user can manage presentation feedback.
     */
    public function manageFeedback(User $user, Presentation $presentation): bool
    {
        // Admin tüm geri bildirimleri yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer geri bildirim yönetebilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view presentation attendees.
     */
    public function viewAttendees(User $user, Presentation $presentation): bool
    {
        return $this->view($user, $presentation);
    }

    /**
     * Determine whether the user can manage presentation recordings.
     */
    public function manageRecordings(User $user, Presentation $presentation): bool
    {
        // Admin tüm kayıtları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer kayıt yönetebilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can approve/reject presentation.
     */
    public function approve(User $user, Presentation $presentation): bool
    {
        // Admin tüm sunumları onaylayabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer onaylayabilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can send notifications about this presentation.
     */
    public function sendNotifications(User $user, Presentation $presentation): bool
    {
        // Admin tüm bildirimleri gönderebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bildirim gönderebilir
        $event = $presentation->programSession->venue->eventDay->event;
        return $user->organizations()
                   ->where('organizations.id', $event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can import presentations.
     */
    public function import(User $user): bool
    {
        // Admin import yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer import yapabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}