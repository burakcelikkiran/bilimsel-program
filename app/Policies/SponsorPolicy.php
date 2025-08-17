<?php

namespace App\Policies;

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sponsors.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar sponsor listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the sponsor.
     */
    public function view(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sponsorları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu sponsorun organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create sponsors.
     */
    public function create(User $user): bool
    {
        // Admin her zaman sponsor oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor sponsor oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the sponsor.
     */
    public function update(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sponsorları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sponsorun organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the sponsor.
     */
    public function delete(User $user, Sponsor $sponsor): bool
    {
        // Aktif sponsorluğu olan sponsor silinemez
        if ($sponsor->programSessions()->exists() || $sponsor->presentations()->exists()) {
            return false;
        }

        // Admin tüm sponsorları silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the sponsor.
     */
    public function restore(User $user, Sponsor $sponsor): bool
    {
        return $this->update($user, $sponsor);
    }

    /**
     * Determine whether the user can permanently delete the sponsor.
     */
    public function forceDelete(User $user, Sponsor $sponsor): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage sponsor logo.
     */
    public function manageLogo(User $user, Sponsor $sponsor): bool
    {
        return $this->update($user, $sponsor);
    }

    /**
     * Determine whether the user can view sponsor contract information.
     */
    public function viewContract(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sözleşme bilgilerini görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer sözleşme bilgilerini görebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage sponsor contract.
     */
    public function manageContract(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sözleşmeleri yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer sözleşme yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can assign sponsor to sessions/presentations.
     */
    public function assignToContent(User $user, Sponsor $sponsor): bool
    {
        return $this->update($user, $sponsor);
    }

    /**
     * Determine whether the user can view sponsor statistics.
     */
    public function viewStatistics(User $user, Sponsor $sponsor): bool
    {
        return $this->view($user, $sponsor);
    }

    /**
     * Determine whether the user can export sponsor data.
     */
    public function export(User $user, Sponsor $sponsor): bool
    {
        return $this->view($user, $sponsor);
    }

    /**
     * Determine whether the user can duplicate the sponsor.
     */
    public function duplicate(User $user, Sponsor $sponsor): bool
    {
        // Sponsoru görebilir ve yeni sponsor oluşturabilir mi?
        return $this->view($user, $sponsor) && $this->create($user);
    }

    /**
     * Determine whether the user can update sponsor sort order.
     */
    public function updateSortOrder(User $user, Sponsor $sponsor): bool
    {
        return $this->update($user, $sponsor);
    }

    /**
     * Determine whether the user can toggle sponsor status.
     */
    public function toggleStatus(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sponsor durumlarını değiştirebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer sponsor durumunu değiştirebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view sponsor financial information.
     */
    public function viewFinancials(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm finansal bilgileri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer finansal bilgileri görebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage sponsor communications.
     */
    public function manageCommunications(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm iletişimleri yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer iletişim yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can generate sponsor reports.
     */
    public function generateReports(User $user, Sponsor $sponsor): bool
    {
        return $this->viewStatistics($user, $sponsor);
    }

    /**
     * Determine whether the user can import sponsors.
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
     * Determine whether the user can manage sponsor benefits.
     */
    public function manageBenefits(User $user, Sponsor $sponsor): bool
    {
        // Admin tüm sponsor avantajlarını yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer avantaj yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}