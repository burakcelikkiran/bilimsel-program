<?php

namespace App\Policies;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParticipantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any participants.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar katılımcı listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the participant.
     */
    public function view(User $user, Participant $participant): bool
    {
        // Admin tüm katılımcıları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu katılımcının organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create participants.
     */
    public function create(User $user): bool
    {
        // Admin her zaman katılımcı oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor katılımcı oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the participant.
     */
    public function update(User $user, Participant $participant): bool
    {
        // Admin tüm katılımcıları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Katılımcının organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the participant.
     */
    public function delete(User $user, Participant $participant): bool
    {
        // Aktif katılımı olan participant silinemez
        if ($participant->hasParticipations()) {
            return false;
        }

        // Admin tüm katılımcıları silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the participant.
     */
    public function restore(User $user, Participant $participant): bool
    {
        return $this->update($user, $participant);
    }

    /**
     * Determine whether the user can permanently delete the participant.
     */
    public function forceDelete(User $user, Participant $participant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view participant contact information.
     */
    public function viewContactInfo(User $user, Participant $participant): bool
    {
        // Admin tüm iletişim bilgilerini görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor iletişim bilgilerini görebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can edit participant contact information.
     */
    public function editContactInfo(User $user, Participant $participant): bool
    {
        // Admin tüm iletişim bilgilerini düzenleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer iletişim bilgilerini düzenleyebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage participant photo.
     */
    public function managePhoto(User $user, Participant $participant): bool
    {
        return $this->update($user, $participant);
    }

    /**
     * Determine whether the user can assign participant to sessions.
     */
    public function assignToSessions(User $user, Participant $participant): bool
    {
        return $this->update($user, $participant);
    }

    /**
     * Determine whether the user can view participant participation history.
     */
    public function viewParticipationHistory(User $user, Participant $participant): bool
    {
        return $this->view($user, $participant);
    }

    /**
     * Determine whether the user can export participant data.
     */
    public function export(User $user, Participant $participant): bool
    {
        return $this->view($user, $participant);
    }

    /**
     * Determine whether the user can duplicate the participant.
     */
    public function duplicate(User $user, Participant $participant): bool
    {
        // Katılımcıyı görebilir ve yeni katılımcı oluşturabilir mi?
        return $this->view($user, $participant) && $this->create($user);
    }

    /**
     * Determine whether the user can import participants.
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

    /**
     * Determine whether the user can search participants.
     */
    public function search(User $user): bool
    {
        // Katılımcı oluşturabilen herkes arama yapabilir
        return $this->create($user);
    }

    /**
     * Determine whether the user can send messages to participant.
     */
    public function sendMessages(User $user, Participant $participant): bool
    {
        // Admin mesaj gönderebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer mesaj gönderebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view participant statistics.
     */
    public function viewStatistics(User $user, Participant $participant): bool
    {
        return $this->view($user, $participant);
    }

    /**
     * Determine whether the user can tag participants.
     */
    public function manageTags(User $user, Participant $participant): bool
    {
        return $this->update($user, $participant);
    }

    /**
     * Determine whether the user can manage participant documents.
     */
    public function manageDocuments(User $user, Participant $participant): bool
    {
        // Admin tüm dokümanları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer doküman yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view participant financial information.
     */
    public function viewFinancials(User $user, Participant $participant): bool
    {
        // Admin tüm finansal bilgileri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer finansal bilgileri görebilir
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}