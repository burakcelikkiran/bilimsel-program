<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\User;

trait OrganizationAware
{
    /**
     * Get current organization ID safely
     */
    protected function getCurrentOrganizationId(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return null;
        }

        // Method 1: Check if user has currentOrganization accessor/method
        try {
            if (property_exists($user, 'currentOrganization') && $user->currentOrganization) {
                return $user->currentOrganization->id;
            }
        } catch (\Exception $e) {
            // Continue to fallback methods
        }

        // Method 2: Check for current_organization_id field
        if (isset($user->current_organization_id) && $user->current_organization_id) {
            $organization = Organization::find($user->current_organization_id);
            if ($organization && $user->hasAccessToOrganization($organization)) {
                return $organization->id;
            }
        }

        // Method 3: Fallback to first organization
        $firstOrganization = $user->organizations()->first();
        if ($firstOrganization) {
            return $firstOrganization->id;
        }

        // Method 4: If admin, get first active organization
        if ($user->isAdmin()) {
            $firstActiveOrg = Organization::where('is_active', true)->first();
            return $firstActiveOrg?->id;
        }

        return null;
    }

    /**
     * Get current organization
     */
    protected function getCurrentOrganization(?User $user = null): ?Organization
    {
        $organizationId = $this->getCurrentOrganizationId($user);
        return $organizationId ? Organization::find($organizationId) : null;
    }

    /**
     * Ensure user has access to organization
     */
    protected function ensureOrganizationAccess(?User $user = null): Organization
    {
        $organization = $this->getCurrentOrganization($user);
        
        if (!$organization) {
            abort(403, 'Erişim yetkisi bulunamadı. Lütfen bir organizasyona dahil olduğunuzdan emin olun.');
        }

        return $organization;
    }

    /**
     * Get organization ID or fail
     */
    protected function getOrganizationIdOrFail(?User $user = null): int
    {
        $organizationId = $this->getCurrentOrganizationId($user);
        
        if (!$organizationId) {
            abort(403, 'Erişim yetkisi bulunamadı. Lütfen bir organizasyona dahil olduğunuzdan emin olun.');
        }

        return $organizationId;
    }

    /**
     * Get user events safely
     */
    protected function getUserEvents(?User $user = null, ?int $organizationId = null): \Illuminate\Database\Eloquent\Collection
    {
        $organizationId = $organizationId ?? $this->getCurrentOrganizationId($user);
        
        if (!$organizationId) {
            return collect([]);
        }

        return \App\Models\Event::where('organization_id', $organizationId)
            ->select('id', 'name', 'title', 'start_date')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Check if user can access resource
     */
    protected function canAccessOrganizationResource($resource, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Check if resource belongs to user's organization
        if (method_exists($resource, 'organization')) {
            return $user->hasAccessToOrganization($resource->organization);
        }

        if (property_exists($resource, 'organization_id')) {
            $organization = Organization::find($resource->organization_id);
            return $organization && $user->hasAccessToOrganization($organization);
        }

        return false;
    }

    /**
     * Scope query to user's organization
     */
    protected function scopeToUserOrganization($query, ?User $user = null, string $organizationField = 'organization_id')
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        if ($user->isAdmin()) {
            return $query; // Admin can see all
        }

        $organizationId = $this->getCurrentOrganizationId($user);
        
        if (!$organizationId) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        return $query->where($organizationField, $organizationId);
    }
}