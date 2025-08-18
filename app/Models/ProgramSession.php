<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgramSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venue_id',
        'category_id', // Ana kategori için (tek kategori varsa)
        'title',
        'description',
        'start_time',
        'end_time',
        'session_type',
        'moderator_title',
        'sponsor_id',
        'is_break',
        'sort_order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_break' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->sort_order)) {
                $model->sort_order = static::where('venue_id', $model->venue_id)->max('sort_order') + 1;
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    /**
     * Ana kategori (tek kategori varsa)
     * Bu ilişki category_id field'ını kullanır
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramSessionCategory::class, 'category_id');
    }

    /**
     * Kategoriler (çok kategori varsa - mevcut many-to-many)
     * Bu ilişki program_session_category_assignments pivot tablosunu kullanır
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProgramSessionCategory::class, 'program_session_category_assignments')
            ->withTimestamps();
    }

    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'program_session_moderators')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('sort_order'); 
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class)->orderBy('start_time')->orderBy('sort_order');
    }

    /**
     * SCOPES
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('start_time')->orderBy('sort_order');
    }

    public function scopeBySessionType($query, $type)
    {
        return $query->where('session_type', $type);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByCategories($query, $categoryIds)
    {
        if (is_array($categoryIds)) {
            return $query->whereIn('category_id', $categoryIds);
        }
        return $query->where('category_id', $categoryIds);
    }

    public function scopeMain($query)
    {
        return $query->where('session_type', 'main');
    }

    public function scopeSatellite($query)
    {
        return $query->where('session_type', 'satellite');
    }

    public function scopeOralPresentation($query)
    {
        return $query->where('session_type', 'oral_presentation');
    }

    public function scopeBreaks($query)
    {
        return $query->where('is_break', true);
    }

    public function scopeNonBreaks($query)
    {
        return $query->where('is_break', false);
    }

    public function scopeSponsored($query)
    {
        return $query->whereNotNull('sponsor_id');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeByVenue($query, $venueId)
    {
        return $query->where('venue_id', $venueId);
    }

    public function scopeByTimeRange($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                    $subQuery->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
        });
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getDurationInMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;
        
        if ($minutes <= 0) {
            return 'Süre belirsiz';
        }
        
        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            $result = $hours . 'sa';
            if ($remainingMinutes > 0) {
                $result .= ' ' . $remainingMinutes . 'dk';
            }
            return $result;
        }

        return $remainingMinutes . 'dk';
    }

    public function getSessionTypeDisplayAttribute(): string
    {
        return match ($this->session_type) {
            'main' => 'Ana Oturum',
            'satellite' => 'Uydu Sempozyumu',
            'oral_presentation' => 'Sözlü Bildiri',
            'break' => 'Ara',
            'special' => 'Özel Oturum',
            'plenary' => 'Genel Oturum',
            'parallel' => 'Paralel Oturum',
            'workshop' => 'Workshop',
            'poster' => 'Poster',
            'lunch' => 'Öğle Arası',
            'social' => 'Sosyal',
            default => ucfirst($this->session_type)
        };
    }

    public function getFormattedModeratorsAttribute(): string
    {
        if (!$this->relationLoaded('moderators')) {
            return '';
        }

        $moderators = $this->moderators->map(function ($moderator) {
            return $moderator->first_name . ' ' . $moderator->last_name;
        })->toArray();

        if (empty($moderators)) {
            return '';
        }

        if (count($moderators) === 1) {
            return ($this->moderator_title ?? 'Moderatör') . ': ' . $moderators[0];
        }

        return ($this->moderator_title ?? 'Moderatör') . 'ları: ' . implode(', ', $moderators);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value ? ucfirst(trim($value)) : $value;
    }

    public function setStartTimeAttribute($value)
    {
        if ($value) {
            $this->attributes['start_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } else {
            $this->attributes['start_time'] = $value;
        }
    }

    public function setEndTimeAttribute($value)
    {
        if ($value) {
            $this->attributes['end_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } else {
            $this->attributes['end_time'] = $value;
        }
    }

    /**
     * HELPER METHODS
     */

    // Backward compatibility method - Laravel accessor olarak duration_in_minutes kullanılmalı
    public function getDurationInMinutes(): int
    {
        return $this->duration_in_minutes;
    }

    public function getTotalPresentations(): int
    {
        return $this->presentations()->count();
    }

    public function getTotalModerators(): int
    {
        return $this->moderators()->count();
    }

    public function hasTimeConflict($excludeSessionId = null): bool
    {
        if (!$this->venue) {
            return false;
        }
        
        return $this->venue->hasTimeConflict(
            $this->start_time,
            $this->end_time,
            $excludeSessionId ?? $this->id
        );
    }

    public function isSponsored(): bool
    {
        return !is_null($this->sponsor_id);
    }

    public function canBeDeleted(): bool
    {
        return $this->presentations()->count() === 0;
    }

    public function hasValidTimeRange(): bool
    {
        return $this->start_time && $this->end_time && $this->start_time < $this->end_time;
    }

    public function overlapsWithSession(ProgramSession $otherSession): bool
    {
        if ($this->venue_id !== $otherSession->venue_id) {
            return false;
        }

        if (!$this->start_time || !$this->end_time || !$otherSession->start_time || !$otherSession->end_time) {
            return false;
        }

        return $this->start_time < $otherSession->end_time &&
            $this->end_time > $otherSession->start_time;
    }

    public function getConflictingSessions(): array
    {
        if (!$this->start_time || !$this->end_time) {
            return [];
        }

        return static::where('venue_id', $this->venue_id)
            ->where('id', '!=', $this->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function ($query) {
                $query->where('start_time', '<', $this->end_time)
                    ->where('end_time', '>', $this->start_time);
            })
            ->get()
            ->toArray();
    }

    /**
     * Get all categories (both direct and many-to-many)
     */
    public function getAllCategories()
    {
        $allCategories = collect();
        
        // Ana kategori varsa ekle
        if ($this->category) {
            $allCategories->push($this->category);
        }
        
        // Many-to-many kategoriler varsa ekle
        if ($this->relationLoaded('categories')) {
            $allCategories = $allCategories->merge($this->categories);
        }
        
        return $allCategories->unique('id');
    }

    /**
     * Check if session has a specific category
     */
    public function hasCategory($categoryId): bool
    {
        // Ana kategori kontrolü
        if ($this->category_id == $categoryId) {
            return true;
        }
        
        // Many-to-many kategoriler kontrolü
        if ($this->relationLoaded('categories')) {
            return $this->categories->contains('id', $categoryId);
        }
        
        return false;
    }

    /**
     * Static helper methods
     */
    public static function getSessionTypes(): array
    {
        return [
            'main' => 'Ana Oturum',
            'satellite' => 'Uydu Sempozyumu',
            'oral_presentation' => 'Sözlü Bildiri',
            'break' => 'Ara',
            'special' => 'Özel Oturum',
            'plenary' => 'Genel Oturum',
            'parallel' => 'Paralel Oturum',
            'workshop' => 'Workshop',
            'poster' => 'Poster',
            'lunch' => 'Öğle Arası',
            'social' => 'Sosyal',
        ];
    }

    public static function getDefaultModeratorTitles(): array
    {
        return [
            'Oturum Başkanı',
            'Oturum Başkanları',
            'Kolaylaştırıcı',
            'Moderatör',
            'Başkan',
        ];
    }

    /**
     * Reorder sessions within a venue
     */
    public static function reorderByVenue($venueId, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            static::where('id', $id)
                ->where('venue_id', $venueId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Move session to different time slot
     */
    public function moveToTimeSlot($newStartTime, $newEndTime): bool
    {
        if (!$this->venue) {
            return false;
        }

        // Check for conflicts first
        $hasConflict = $this->venue->hasTimeConflict($newStartTime, $newEndTime, $this->id);

        if ($hasConflict) {
            return false;
        }

        $this->update([
            'start_time' => $newStartTime,
            'end_time' => $newEndTime,
        ]);

        return true;
    }

    /**
     * Duplicate session
     */
    public function duplicate(array $overrides = []): self
    {
        $attributes = $this->getAttributes();

        // Remove unique attributes
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        // Apply overrides
        $attributes = array_merge($attributes, $overrides);

        // Create new session
        $newSession = static::create($attributes);

        // Copy moderators
        if ($this->relationLoaded('moderators')) {
            foreach ($this->moderators as $moderator) {
                $newSession->moderators()->attach($moderator->id, [
                    'sort_order' => $moderator->pivot->sort_order ?? 0
                ]);
            }
        }

        // Copy many-to-many categories
        if ($this->relationLoaded('categories')) {
            $newSession->categories()->sync($this->categories->pluck('id'));
        }

        return $newSession;
    }

    /**
     * Get the event through venue -> eventDay -> event
     */
    public function event()
    {
        return $this->hasOneThrough(
            Event::class,
            EventDay::class,
            'id',          // event_days tablosundaki local key
            'id',          // events tablosundaki local key
            'venue_id',    // program_sessions tablosundaki foreign key
            'event_id'     // event_days tablosundaki foreign key
        )->join('venues', function ($join) {
            $join->on('event_days.id', '=', 'venues.event_day_id')
                 ->where('venues.id', '=', DB::raw('program_sessions.venue_id'));
        });
    }

    public function getEventAttribute()
    {
        return $this->venue?->eventDay?->event;
    }

    // EventDay relationship'i de ekleyebilirsiniz:
    public function eventDay()
    {
        return $this->hasOneThrough(
            EventDay::class,
            Venue::class,
            'id',               // Venue tablosundaki local key
            'id',               // EventDay tablosundaki local key
            'venue_id',         // ProgramSession tablosundaki foreign key
            'event_day_id'      // Venue tablosundaki foreign key
        );
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['formatted_time_range'] = $this->formatted_time_range;
        $array['duration_in_minutes'] = $this->duration_in_minutes;
        $array['formatted_duration'] = $this->formatted_duration;
        $array['session_type_display'] = $this->session_type_display;
        $array['formatted_moderators'] = $this->formatted_moderators;
        $array['total_presentations'] = $this->getTotalPresentations();
        $array['total_moderators'] = $this->getTotalModerators();
        $array['is_sponsored'] = $this->isSponsored();
        return $array;
    }
}