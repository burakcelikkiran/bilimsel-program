<?php

namespace App\Traits;

use App\Models\Activity;
use App\Models\Organization;
use App\Models\User;

trait LogsActivity
{
    /**
     * Boot the trait and register model events
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', $model->getDirty());
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * Log an activity for this model
     */
    public function logActivity(string $action, array $properties = [])
    {
        $user = auth()->user();
        if (!$user) {
            return; // No user authenticated, skip logging
        }

        $organization = $this->getOrganizationForActivity();
        $description = $this->generateActivityDescription($action);
        $type = $this->getActivityType($action);

        Activity::log(
            $type,
            $description,
            $this,
            $user,
            $organization,
            $properties
        );
    }

    /**
     * Get the organization related to this model for activity logging
     */
    protected function getOrganizationForActivity(): ?Organization
    {
        // Try different common organization relationship patterns
        if (method_exists($this, 'organization') && $this->organization) {
            return $this->organization;
        }

        if (property_exists($this, 'organization_id') && $this->organization_id) {
            return Organization::find($this->organization_id);
        }

        // For nested relationships (e.g., Session -> Venue -> EventDay -> Event -> Organization)
        if (method_exists($this, 'venue') && $this->venue?->eventDay?->event?->organization) {
            return $this->venue->eventDay->event->organization;
        }

        // For Event model
        if (method_exists($this, 'event') && $this->event?->organization) {
            return $this->event->organization;
        }

        return null;
    }

    /**
     * Generate a human-readable description for the activity
     */
    protected function generateActivityDescription(string $action): string
    {
        $modelName = $this->getModelDisplayName();
        $itemName = $this->getDisplayName();
        
        $actionDescriptions = [
            'created' => "'{$itemName}' {$modelName} oluşturuldu",
            'updated' => "'{$itemName}' {$modelName} güncellendi",
            'deleted' => "'{$itemName}' {$modelName} silindi",
            'published' => "'{$itemName}' {$modelName} yayınlandı",
            'unpublished' => "'{$itemName}' {$modelName} yayından kaldırıldı",
        ];

        return $actionDescriptions[$action] ?? "'{$itemName}' {$modelName} {$action}";
    }

    /**
     * Get the activity type constant based on action
     */
    protected function getActivityType(string $action): string
    {
        $modelType = strtolower(class_basename($this));
        return "{$modelType}_{$action}";
    }

    /**
     * Get a human-readable name for this model type
     */
    protected function getModelDisplayName(): string
    {
        $modelDisplayNames = [
            'Event' => 'etkinliği',
            'ProgramSession' => 'oturumu',
            'Presentation' => 'sunumu',
            'Participant' => 'katılımcısı',
            'Venue' => 'salonu',
            'Sponsor' => 'sponsoru',
            'Organization' => 'organizasyonu',
            'EventDay' => 'etkinlik günü',
            'ProgramSessionCategory' => 'oturum kategorisi',
        ];

        $className = class_basename($this);
        return $modelDisplayNames[$className] ?? strtolower($className);
    }

    /**
     * Get a display name for this specific model instance
     */
    protected function getDisplayName(): string
    {
        $className = class_basename($this);
        
        // Define preferred fields for each model type
        switch ($className) {
            case 'Event':
                return $this->title ?: ($this->name ?: "#{$this->id}");
            case 'ProgramSession':
                return $this->title ?: "#{$this->id}";
            case 'Participant':
                return $this->full_name ?: (trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: "#{$this->id}");
            case 'Venue':
                return $this->display_name ?: ($this->name ?: "#{$this->id}");
            case 'Sponsor':
            case 'Organization':
                return $this->name ?: "#{$this->id}";
            default:
                // Try common name fields for other models
                $nameFields = ['name', 'title', 'full_name', 'display_name', 'label'];
                
                foreach ($nameFields as $field) {
                    if (property_exists($this, $field) && !empty($this->$field)) {
                        return $this->$field;
                    }
                }
                
                return "#{$this->id}";
        }
    }

    /**
     * Log a custom activity
     */
    public function logCustomActivity(string $type, string $description, array $properties = [])
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $organization = $this->getOrganizationForActivity();

        Activity::log(
            $type,
            $description,
            $this,
            $user,
            $organization,
            $properties
        );
    }

    /**
     * Log a publish activity
     */
    public function logPublished()
    {
        $this->logActivity('published');
    }

    /**
     * Log an unpublish activity
     */
    public function logUnpublished()
    {
        $this->logActivity('unpublished');
    }
}