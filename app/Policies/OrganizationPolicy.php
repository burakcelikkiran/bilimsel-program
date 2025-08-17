<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any organizations.
     */
    public function viewAny(User $user): bool
    {
        // Admin her organizasyonu görebilir
        // Diğer kullanıcılar sadece bağlı oldukları organizasyonları görebilir
        return $user->isAdmin() || $user->organizations()->exists();
    }

    /**
     * Determine whether the user can view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        // Admin tüm organizasyonları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu organizasyona bağlı mı?
        return $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Determine whether the user can create organizations.
     */
    public function create(User $user): bool
    {
        // Sadece admin yeni organizasyon oluşturabilir
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        // Admin tüm organizasyonları güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu organizasyonda organizer rolünde mi?
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Sadece admin organizasyon silebilir
        if (!$user->isAdmin()) {
            return false;
        }

        // Aktif etkinlikleri olan organizasyon silinemez
        if ($organization->events()->published()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the organization.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the organization.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage users in the organization.
     */
    public function manageUsers(User $user, Organization $organization): bool
    {
        // Admin tüm organizasyonlarda kullanıcı yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer kendi organizasyonunda kullanıcı yönetebilir
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view organization statistics.
     */
    public function viewStatistics(User $user, Organization $organization): bool
    {
        // Admin tüm istatistikleri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor bu organizasyonun istatistiklerini görebilir
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can export organization data.
     */
    public function export(User $user, Organization $organization): bool
    {
        // Admin tüm veriyi export edebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer kendi organizasyonunun verilerini export edebilir
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage organization settings.
     */
    public function manageSettings(User $user, Organization $organization): bool
    {
        // Admin tüm ayarları yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer ayarları değiştirebilir
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view organization billing.
     */
    public function viewBilling(User $user, Organization $organization): bool
    {
        // Admin tüm faturaları görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer fatura bilgilerini görebilir
        return $user->organizations()
                   ->where('organizations.id', $organization->id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}