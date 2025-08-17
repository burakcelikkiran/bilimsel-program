<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileUploadPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can upload participant photos.
     */
    public function uploadParticipantPhoto(User $user): bool
    {
        // Admin her zaman dosya yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor katılımcı fotoğrafı yükleyebilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can upload sponsor logos.
     */
    public function uploadSponsorLogo(User $user): bool
    {
        // Admin her zaman logo yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor sponsor logosu yükleyebilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can upload presentation files.
     */
    public function uploadPresentationFile(User $user): bool
    {
        // Admin her zaman sunum dosyası yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor sunum dosyası yükleyebilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can upload event banners/posters.
     */
    public function uploadEventBanner(User $user): bool
    {
        // Admin her zaman etkinlik görseli yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer etkinlik görseli yükleyebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can upload organization logos.
     */
    public function uploadOrganizationLogo(User $user): bool
    {
        // Admin her zaman organizasyon logosu yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer organizasyon logosu yükleyebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can delete uploaded files.
     */
    public function deleteFile(User $user): bool
    {
        // Admin her zaman dosya silebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer dosya silebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view file information.
     */
    public function viewFileInfo(User $user): bool
    {
        // Tüm kullanıcılar dosya bilgilerini görebilir
        return $user->organizations()->exists();
    }

    /**
     * Determine whether the user can bulk upload files.
     */
    public function bulkUpload(User $user): bool
    {
        // Admin bulk upload yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer bulk upload yapabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can upload CSV/Excel import files.
     */
    public function uploadImportFile(User $user): bool
    {
        // Admin import dosyası yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer import dosyası yükleyebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can upload venue documents.
     */
    public function uploadVenueDocument(User $user): bool
    {
        // Admin venue dokümanı yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer ve editor venue dokümanı yükleyebilir
        return $user->organizations()
                   ->whereIn('role', ['organizer', 'editor'])
                   ->exists();
    }

    /**
     * Determine whether the user can upload contract files.
     */
    public function uploadContractFile(User $user): bool
    {
        // Admin sözleşme dosyası yükleyebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Sadece organizer sözleşme dosyası yükleyebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can manage file permissions.
     */
    public function manageFilePermissions(User $user): bool
    {
        // Admin dosya izinlerini yönetebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer dosya izinlerini yönetebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can view file upload history.
     */
    public function viewUploadHistory(User $user): bool
    {
        // Admin tüm upload geçmişini görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer upload geçmişini görebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can compress/optimize uploaded files.
     */
    public function optimizeFiles(User $user): bool
    {
        return $this->uploadParticipantPhoto($user);
    }

    /**
     * Determine whether the user can generate file thumbnails.
     */
    public function generateThumbnails(User $user): bool
    {
        return $this->uploadParticipantPhoto($user);
    }

    /**
     * Determine whether the user can backup files.
     */
    public function backupFiles(User $user): bool
    {
        // Admin dosya yedeklemesi yapabilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer dosya yedeklemesi yapabilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }

    /**
     * Determine whether the user can restore files from backup.
     */
    public function restoreFiles(User $user): bool
    {
        return $this->backupFiles($user);
    }

    /**
     * Determine whether the user can access file analytics.
     */
    public function viewFileAnalytics(User $user): bool
    {
        // Admin dosya analitiğini görebilir
        if ($user->isAdmin()) {
            return true;
        }

        // Organizer dosya analitiğini görebilir
        return $user->organizations()
                   ->wherePivot('role', 'organizer')
                   ->exists();
    }
}