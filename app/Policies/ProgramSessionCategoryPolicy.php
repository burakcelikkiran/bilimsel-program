<?php

namespace App\Policies;

use App\Models\ProgramSessionCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramSessionCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any program session categories.
     */
    public function viewAny(User $user): bool
    {
        // Tüm kullanıcılar kategori listesini görebilir (kendi erişimlerine göre)
        return true;
    }

    /**
     * Determine whether the user can view the program session category.
     */
    public function view(User $user, ProgramSessionCategory $category): bool
    {
        // Admin tüm kategorileri görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kullanıcı bu kategorinin etkinliğinin organizasyonuna bağlı mı?
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->exists();
    }

    /**
     * Determine whether the user can create program session categories.
     */
    public function create(User $user): bool
    {
        // Admin her zaman kategori oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor kategori oluşturabilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can update the program session category.
     */
    public function update(User $user, ProgramSessionCategory $category): bool
    {
        // Admin tüm kategorileri güncelleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Kategorinin etkinliğinin organizasyonunda organizer veya editor rolü var mı?
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can delete the program session category.
     */
    public function delete(User $user, ProgramSessionCategory $category): bool
    {
        // Kullanılmakta olan kategori silinemez
        if ($category->programSessions()->exists()) {
            return false;
        }

        // Admin tüm kategorileri silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer silebilir
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore the program session category.
     */
    public function restore(User $user, ProgramSessionCategory $category): bool
    {
        return $this->update($user, $category);
    }

    /**
     * Determine whether the user can permanently delete the program session category.
     */
    public function forceDelete(User $user, ProgramSessionCategory $category): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can duplicate the category.
     */
    public function duplicate(User $user, ProgramSessionCategory $category): bool
    {
        // Kategoriyi görebilir ve yeni kategori oluşturabilir mi?
        return $this->view($user, $category) && $this->create($user);
    }

    /**
     * Determine whether the user can toggle category status.
     */
    public function toggleStatus(User $user, ProgramSessionCategory $category): bool
    {
        // Admin tüm kategori durumlarını değiştirebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer kategori durumunu değiştirebilir
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can update category sort order.
     */
    public function updateSortOrder(User $user, ProgramSessionCategory $category): bool
    {
        return $this->update($user, $category);
    }

    /**
     * Determine whether the user can assign sessions to this category.
     */
    public function assignSessions(User $user, ProgramSessionCategory $category): bool
    {
        return $this->update($user, $category);
    }

    /**
     * Determine whether the user can view category statistics.
     */
    public function viewStatistics(User $user, ProgramSessionCategory $category): bool
    {
        return $this->view($user, $category);
    }

    /**
     * Determine whether the user can export category data.
     */
    public function export(User $user, ProgramSessionCategory $category): bool
    {
        return $this->view($user, $category);
    }

    /**
     * Determine whether the user can manage category colors and icons.
     */
    public function manageAppearance(User $user, ProgramSessionCategory $category): bool
    {
        return $this->update($user, $category);
    }

    /**
     * Determine whether the user can create predefined categories for an event.
     */
    public function createPredefined(User $user): bool
    {
        // Admin predefined kategori oluşturabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer predefined kategori oluşturabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can bulk update categories.
     */
    public function bulkUpdate(User $user): bool
    {
        // Admin bulk update yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bulk update yapabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage category templates.
     */
    public function manageTemplates(User $user): bool
    {
        // Admin template yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer template yönetebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view category usage analytics.
     */
    public function viewAnalytics(User $user, ProgramSessionCategory $category): bool
    {
        return $this->view($user, $category);
    }

    /**
     * Determine whether the user can merge categories.
     */
    public function merge(User $user, ProgramSessionCategory $category): bool
    {
        // Admin kategori birleştirebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer kategori birleştirebilir
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}