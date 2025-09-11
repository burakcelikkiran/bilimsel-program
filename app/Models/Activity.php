<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $fillable = [
        'type',
        'description',
        'subject_type',
        'subject_id',
        'user_id',
        'organization_id',
        'properties',
        'performed_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the subject of the activity (polymorphic relation)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization related to the activity
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Activity types constants
     */
    const TYPE_EVENT_CREATED = 'event_created';
    const TYPE_EVENT_UPDATED = 'event_updated';
    const TYPE_EVENT_DELETED = 'event_deleted';
    const TYPE_EVENT_PUBLISHED = 'event_published';
    const TYPE_EVENT_UNPUBLISHED = 'event_unpublished';
    
    const TYPE_SESSION_CREATED = 'session_created';
    const TYPE_SESSION_UPDATED = 'session_updated';
    const TYPE_SESSION_DELETED = 'session_deleted';
    
    const TYPE_PRESENTATION_CREATED = 'presentation_created';
    const TYPE_PRESENTATION_UPDATED = 'presentation_updated';
    const TYPE_PRESENTATION_DELETED = 'presentation_deleted';
    
    const TYPE_PARTICIPANT_CREATED = 'participant_created';
    const TYPE_PARTICIPANT_UPDATED = 'participant_updated';
    const TYPE_PARTICIPANT_DELETED = 'participant_deleted';
    
    const TYPE_VENUE_CREATED = 'venue_created';
    const TYPE_VENUE_UPDATED = 'venue_updated';
    const TYPE_VENUE_DELETED = 'venue_deleted';
    
    const TYPE_SPONSOR_CREATED = 'sponsor_created';
    const TYPE_SPONSOR_UPDATED = 'sponsor_updated';
    const TYPE_SPONSOR_DELETED = 'sponsor_deleted';
    
    const TYPE_ORGANIZATION_CREATED = 'organization_created';
    const TYPE_ORGANIZATION_UPDATED = 'organization_updated';
    const TYPE_ORGANIZATION_DELETED = 'organization_deleted';

    /**
     * Get all activity types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_EVENT_CREATED => 'Etkinlik Oluşturuldu',
            self::TYPE_EVENT_UPDATED => 'Etkinlik Güncellendi',
            self::TYPE_EVENT_DELETED => 'Etkinlik Silindi',
            self::TYPE_EVENT_PUBLISHED => 'Etkinlik Yayınlandı',
            self::TYPE_EVENT_UNPUBLISHED => 'Etkinlik Yayından Kaldırıldı',
            
            self::TYPE_SESSION_CREATED => 'Oturum Oluşturuldu',
            self::TYPE_SESSION_UPDATED => 'Oturum Güncellendi',
            self::TYPE_SESSION_DELETED => 'Oturum Silindi',
            
            self::TYPE_PRESENTATION_CREATED => 'Sunum Oluşturuldu',
            self::TYPE_PRESENTATION_UPDATED => 'Sunum Güncellendi',
            self::TYPE_PRESENTATION_DELETED => 'Sunum Silindi',
            
            self::TYPE_PARTICIPANT_CREATED => 'Katılımcı Eklendi',
            self::TYPE_PARTICIPANT_UPDATED => 'Katılımcı Güncellendi',
            self::TYPE_PARTICIPANT_DELETED => 'Katılımcı Silindi',
            
            self::TYPE_VENUE_CREATED => 'Salon Oluşturuldu',
            self::TYPE_VENUE_UPDATED => 'Salon Güncellendi',
            self::TYPE_VENUE_DELETED => 'Salon Silindi',
            
            self::TYPE_SPONSOR_CREATED => 'Sponsor Eklendi',
            self::TYPE_SPONSOR_UPDATED => 'Sponsor Güncellendi',
            self::TYPE_SPONSOR_DELETED => 'Sponsor Silindi',
            
            self::TYPE_ORGANIZATION_CREATED => 'Organizasyon Oluşturuldu',
            self::TYPE_ORGANIZATION_UPDATED => 'Organizasyon Güncellendi',
            self::TYPE_ORGANIZATION_DELETED => 'Organizasyon Silindi',
        ];
    }

    /**
     * Get the human readable type label
     */
    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Create an activity log entry
     */
    public static function log(
        string $type,
        string $description,
        Model $subject,
        User $user,
        ?Organization $organization = null,
        ?array $properties = null
    ): self {
        return self::create([
            'type' => $type,
            'description' => $description,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'properties' => $properties,
            'performed_at' => now(),
        ]);
    }

    /**
     * Scope to get activities for a specific user's organizations
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $organizationIds = $user->organizations()->pluck('organizations.id');
        return $query->whereIn('organization_id', $organizationIds);
    }

    /**
     * Scope to get recent activities
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('performed_at', 'desc')->limit($limit);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string|array $types)
    {
        if (is_string($types)) {
            return $query->where('type', $types);
        }

        return $query->whereIn('type', $types);
    }
}
