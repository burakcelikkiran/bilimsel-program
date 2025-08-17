<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Presentation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_session_id',
        'title',
        'abstract',
        'start_time',
        'end_time',
        'presentation_type',
        'sponsor_id',
        'sort_order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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
            // UUID kısmını kaldırdık - normal auto-increment ID kullanıyoruz
            if (is_null($model->sort_order)) {
                $maxOrder = static::where('program_session_id', $model->program_session_id)->max('sort_order');
                $model->sort_order = $maxOrder ? $maxOrder + 1 : 1;
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function programSession(): BelongsTo
    {
        return $this->belongsTo(ProgramSession::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'presentation_speakers')
            ->withPivot(['speaker_role', 'sort_order'])
            ->withTimestamps()
            ->orderBy('presentation_speakers.sort_order'); 
    }


    /**
     * SCOPES
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('start_time')->orderBy('sort_order');
    }

    public function scopeByPresentationType($query, $type)
    {
        return $query->where('presentation_type', $type);
    }

    public function scopeKeynote($query)
    {
        return $query->where('presentation_type', 'keynote');
    }

    public function scopeOral($query)
    {
        return $query->where('presentation_type', 'oral');
    }

    public function scopeCasePresentation($query)
    {
        return $query->where('presentation_type', 'case_presentation');
    }

    public function scopeSymposium($query)
    {
        return $query->where('presentation_type', 'symposium');
    }

    public function scopeSponsored($query)
    {
        return $query->whereNotNull('sponsor_id');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('abstract', 'like', "%{$term}%");
        });
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('program_session_id', $sessionId);
    }

    public function scopeWithSpeakers($query)
    {
        return $query->whereHas('speakers');
    }

    public function scopeWithoutSpeakers($query)
    {
        return $query->whereDoesntHave('speakers');
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;
        
        if ($minutes >= 60) {
            $hours = intval($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return $hours . 'sa ' . ($remainingMinutes > 0 ? $remainingMinutes . 'dk' : '');
        }

        return $minutes . 'dk';
    }

    public function getPresentationTypeDisplayAttribute(): string
    {
        return match($this->presentation_type) {
            'keynote' => 'Açılış Konuşması',
            'oral' => 'Sözlü Sunum',
            'case_presentation' => 'Olgu Sunumu',
            'symposium' => 'Sempozyum',
            default => ucfirst($this->presentation_type)
        };
    }

    public function getShortAbstractAttribute(): string
    {
        if (!$this->abstract) {
            return '';
        }
        return Str::limit($this->abstract, 200);
    }

    public function getPrimarySpeakersAttribute()
    {
        return $this->speakers()->wherePivot('speaker_role', 'primary')->get();
    }

    public function getCoSpeakersAttribute()
    {
        return $this->speakers()->wherePivot('speaker_role', 'co_speaker')->get();
    }

    public function getDiscussantsAttribute()
    {
        return $this->speakers()->wherePivot('speaker_role', 'discussant')->get();
    }

    public function getFormattedSpeakersAttribute(): string
    {
        $speakers = $this->speakers->groupBy('pivot.speaker_role');
        $formatted = [];

        if ($speakers->has('primary')) {
            $primaryNames = $speakers['primary']->pluck('full_name')->toArray();
            $formatted[] = implode(', ', $primaryNames);
        }

        if ($speakers->has('co_speaker')) {
            $coSpeakerNames = $speakers['co_speaker']->pluck('full_name')->toArray();
            $formatted[] = 'Ko-konuşmacı: ' . implode(', ', $coSpeakerNames);
        }

        if ($speakers->has('discussant')) {
            $discussantNames = $speakers['discussant']->pluck('full_name')->toArray();
            $formatted[] = 'Tartışmacı: ' . implode(', ', $discussantNames);
        }

        return implode(' | ', $formatted);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst(trim($value));
    }

    public function setStartTimeAttribute($value)
    {
        // Daha esnek time parsing
        if ($value instanceof Carbon) {
            $this->attributes['start_time'] = $value->format('H:i:s');
        } elseif (is_string($value)) {
            // H:i:s formatı varsa direkt kullan
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $this->attributes['start_time'] = $value;
            } elseif (preg_match('/^\d{2}:\d{2}$/', $value)) {
                // H:i formatı varsa :00 ekle
                $this->attributes['start_time'] = $value . ':00';
            } else {
                // Carbon ile parse et
                $this->attributes['start_time'] = Carbon::parse($value)->format('H:i:s');
            }
        }
    }

    public function setEndTimeAttribute($value)
    {
        // Daha esnek time parsing
        if ($value instanceof Carbon) {
            $this->attributes['end_time'] = $value->format('H:i:s');
        } elseif (is_string($value)) {
            // H:i:s formatı varsa direkt kullan
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $this->attributes['end_time'] = $value;
            } elseif (preg_match('/^\d{2}:\d{2}$/', $value)) {
                // H:i formatı varsa :00 ekle
                $this->attributes['end_time'] = $value . ':00';
            } else {
                // Carbon ile parse et
                $this->attributes['end_time'] = Carbon::parse($value)->format('H:i:s');
            }
        }
    }

    /**
     * HELPER METHODS
     */
    public function getTotalSpeakers(): int
    {
        return $this->speakers()->count();
    }

    public function getPrimarySpeakersCount(): int
    {
        return $this->speakers()->wherePivot('speaker_role', 'primary')->count();
    }

    public function getCoSpeakersCount(): int
    {
        return $this->speakers()->wherePivot('speaker_role', 'co_speaker')->count();
    }

    public function getDiscussantsCount(): int
    {
        return $this->speakers()->wherePivot('speaker_role', 'discussant')->count();
    }

    public function isSponsored(): bool
    {
        return !is_null($this->sponsor_id);
    }

    public function hasValidTimeRange(): bool
    {
        return $this->start_time < $this->end_time;
    }

    public function isWithinSessionTime(): bool
    {
        $session = $this->programSession;
        return $this->start_time >= $session->start_time && 
               $this->end_time <= $session->end_time;
    }

    public function hasTimeConflict($excludePresentationId = null): bool
    {
        return static::where('program_session_id', $this->program_session_id)
                    ->when($excludePresentationId, fn($q) => $q->where('id', '!=', $excludePresentationId))
                    ->where('id', '!=', $this->id)
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            // New presentation starts during existing presentation
                            $q->where('start_time', '<=', $this->start_time)
                              ->where('end_time', '>', $this->start_time);
                        })->orWhere(function ($q) {
                            // New presentation ends during existing presentation
                            $q->where('start_time', '<', $this->end_time)
                              ->where('end_time', '>=', $this->end_time);
                        })->orWhere(function ($q) {
                            // New presentation encompasses existing presentation
                            $q->where('start_time', '>=', $this->start_time)
                              ->where('end_time', '<=', $this->end_time);
                        });
                    })
                    ->exists();
    }

    public function getConflictingPresentations(): array
    {
        return static::where('program_session_id', $this->program_session_id)
                    ->where('id', '!=', $this->id)
                    ->where(function ($query) {
                        $query->where('start_time', '<', $this->end_time)
                              ->where('end_time', '>', $this->start_time);
                    })
                    ->get()
                    ->toArray();
    }

    /**
     * Static helper methods
     */
    public static function getPresentationTypes(): array
    {
        return [
            'keynote' => 'Açılış Konuşması',
            'oral' => 'Sözlü Sunum',
            'case_presentation' => 'Olgu Sunumu',
            'symposium' => 'Sempozyum',
        ];
    }

    public static function getSpeakerRoles(): array
    {
        return [
            'primary' => 'Ana Konuşmacı',
            'co_speaker' => 'Ko-konuşmacı',
            'discussant' => 'Tartışmacı',
        ];
    }

    /**
     * Reorder presentations within a session
     */
    public static function reorderBySession($sessionId, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            static::where('id', $id)
                  ->where('program_session_id', $sessionId)
                  ->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Move presentation to different time slot
     */
    public function moveToTimeSlot($newStartTime, $newEndTime): bool
    {
        // Check if new time is within session boundaries
        $session = $this->programSession;
        if ($newStartTime < $session->start_time || $newEndTime > $session->end_time) {
            return false;
        }

        // Check for conflicts with other presentations
        $hasConflict = static::where('program_session_id', $this->program_session_id)
                            ->where('id', '!=', $this->id)
                            ->where(function ($query) use ($newStartTime, $newEndTime) {
                                $query->where('start_time', '<', $newEndTime)
                                      ->where('end_time', '>', $newStartTime);
                            })
                            ->exists();

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
     * Duplicate presentation
     */
    public function duplicate(array $overrides = []): self
    {
        $attributes = $this->getAttributes();
        
        // Remove unique attributes
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at']);
        
        // Apply overrides
        $attributes = array_merge($attributes, $overrides);
        
        // Create new presentation
        $newPresentation = static::create($attributes);
        
        // Copy speakers
        foreach ($this->speakers as $speaker) {
            $newPresentation->speakers()->attach($speaker->id, [
                'speaker_role' => $speaker->pivot->speaker_role,
                'sort_order' => $speaker->pivot->sort_order
            ]);
        }
        
        return $newPresentation;
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
        $array['presentation_type_display'] = $this->presentation_type_display;
        $array['short_abstract'] = $this->short_abstract;
        $array['formatted_speakers'] = $this->formatted_speakers;
        $array['total_speakers'] = $this->getTotalSpeakers();
        $array['primary_speakers_count'] = $this->getPrimarySpeakersCount();
        $array['co_speakers_count'] = $this->getCoSpeakersCount();
        $array['discussants_count'] = $this->getDiscussantsCount();
        $array['is_sponsored'] = $this->isSponsored();
        return $array;
    }
}